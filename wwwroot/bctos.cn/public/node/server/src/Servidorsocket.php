<?php
/*
** PHP SSH2 Web Client
** Autor: Jose Joaquin Anton
** Email: roke@roke.es
**
** License: The MIT License -> https://opensource.org/licenses/mit-license.php
**  Copyright (c) 2018 Jose Joaquin Anton
**
**  Se concede permiso, libre de cargos, a cualquier persona que obtenga una copia de este software y de los archivos de documentación asociados
**  (el "Software"), para utilizar el Software sin restricción, incluyendo sin limitación los derechos a usar, copiar, modificar, fusionar, publicar,
**  distribuir, sublicenciar, y/o vender copias del Software, y a permitir a las personas a las que se les proporcione el Software a hacer lo mismo,
**  sujeto a las siguientes condiciones:
**
**  El aviso de copyright anterior y este aviso de permiso se incluirán en todas las copias o partes sustanciales del Software.
**
**  EL SOFTWARE SE PROPORCIONA "TAL CUAL", SIN GARANTÍA DE NINGÚN TIPO, EXPRESA O IMPLÍCITA, INCLUYENDO PERO NO LIMITADA A GARANTÍAS DE
**  COMERCIALIZACIÓN, IDONEIDAD PARA UN PROPÓSITO PARTICULAR Y NO INFRACCIÓN. EN NINGÚN CASO LOS AUTORES O PROPIETARIOS DE LOS DERECHOS DE
**  AUTOR SERÁN RESPONSABLES DE NINGUNA RECLAMACIÓN, DAÑOS U OTRAS RESPONSABILIDADES, YA SEA EN UNA ACCIÓN DE CONTRATO, AGRAVIO O CUALQUIER OTRO
**  MOTIVO, DERIVADAS DE, FUERA DE O EN CONEXIÓN CON EL SOFTWARE O SU USO U OTRO TIPO DE ACCIONES EN EL SOFTWARE.
*/

namespace MyApp;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Servidorsocket implements MessageComponentInterface
{
    protected $clients;
    protected $connection = array();
    protected $shell = array();
    protected $conectado = array();
    const COLS = 80;
    const ROWS = 24;
    var $web_path = '';

    public function __construct()
    {

        $this->web_path = dirname(str_replace('/public/node/server/src', '', __FILE__));
        require_once($this->web_path . "/config/weiphp_define.php");
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);
        $this->connection[$conn->resourceId] = null;
        $this->shell[$conn->resourceId] = null;
        $this->conectado[$conn->resourceId] = null;
    }

    private function shellDeal($data, $param = '')
    {
        $res = '';
        switch (trim($data)) {
            case 'check_status':
                $res = "bash -l {$this->web_path}/scripts/fabric2/status.sh";
                break;
            case 'start_net':
                $res = "bash -l {$this->web_path}/scripts/fabric2/start.sh";
                break;
            case 'close_net':
                $res = chr(0x03) . "\nbash -l {$this->web_path}/scripts/fabric2/stop.sh";
                break;
            case 'deploy':
                $res = "bash -l {$this->web_path}/scripts/fabric2/deployCC.sh";
                break;
            case 'test':
                $res = "bash -l {$this->web_path}/scripts/fabric2/execCC.sh";
                break;
            case 'sys_update':
                $config = require $this->web_path . '/config/database.php';
                $config = $config['connections']['mysql'];
                $res = "bash -l {$this->web_path}/scripts/sys/update.sh {$config['username']} {$config['password']} {$config['hostname']} {$config['hostport']} {$config['database']}";
                break;
            case 'tail':
                $res = "tail -f";
                break;
            default:
                $res = '';
        }
        if (!empty($param)) {
            $res .= ' ' . $param;
        }
        return $res . "\n";
    }
    public function onMessage(ConnectionInterface $from, $msg)
    {
        $data = json_decode($msg, true);
        switch (key($data)) {
            case 'data':
                fwrite($this->shell[$from->resourceId], $data['data']['data']);
                usleep(800);
                while ($line = fgets($this->shell[$from->resourceId])) {
                    $from->send(mb_convert_encoding($line, "UTF-8"));
                }
                break;
            case 'post':
                $post = $data['deploy'];
                //dump($post);
                $command = "cd {$post['path']}\n";
                if ($post['lang'] == 'golang') {
                    $command .= "go env -w GOPROXY=https://goproxy.cn,direct\ngo build\n";
                }
                $command .= "CORE_PEER_ADDRESS=peer:7051 CORE_CHAINCODE_ID_NAME=mycc:0 ./{$post['name']}\n";
                fwrite($this->shell[$from->resourceId], $command);
                usleep(800);
                while ($line = fgets($this->shell[$from->resourceId])) {
                    $from->send(mb_convert_encoding($line, "UTF-8"));
                }
                break;
            case 'get':
                $command = $this->shellDeal($data['get']);
                $stream = ssh2_exec($this->connection[$from->resourceId], $command);
                stream_set_blocking($stream, true);
                $output = stream_get_contents($stream);
                fclose($stream);
                $this->web_msg($output);
                break;
            case 'json':
                var_export($data['json']);
                $json = json_decode($data['json'], true);
                var_export($json);
                isset($json['param']) || $json['param'] = '';
                $command = $this->shellDeal($json['data'], $json['param']);
                var_export($command);
                fwrite($this->shell[$from->resourceId], $command);
                usleep(800);
                while ($line = fgets($this->shell[$from->resourceId])) {
                    $from->send(mb_convert_encoding($line, "UTF-8"));
                }
                break;
            case 'line':
                $command = $this->shellDeal($data['line']);
                //file_put_contents('/tmp/chaincode.txt', $command . PHP_EOL, FILE_APPEND);
                fwrite($this->shell[$from->resourceId], $command);
                usleep(800);
                while ($line = fgets($this->shell[$from->resourceId])) {
                    $from->send(mb_convert_encoding($line, "UTF-8"));
                }
                break;
            case 'auth':
                if ($this->connectSSH(SSH_IP, 22, 'root', SSH_PAWD, $from)) {
//                    $from->send(mb_convert_encoding("Connected....", "UTF-8"));
//                    while ($line = fgets($this->shell[$from->resourceId])) {
//                        $from->send(mb_convert_encoding($line, "UTF-8"));
//                    }
                } else {
                    $from->send(mb_convert_encoding("Error, can not connect to the server. Check the credentials", "UTF-8"));
                    $from->close();
                }
                break;
            default:
                if ($this->conectado[$from->resourceId]) {
                    while ($line = fgets($this->shell[$from->resourceId])) {
                        $from->send(mb_convert_encoding($line, "UTF-8"));
                    }
                }
                break;
        }
    }

    function web_msg($content)
    {
        // 指明给谁推送，为空表示向所有在线用户推送
        $to_uid = 1;
        // 推送的url地址，使用自己的服务器地址
        $push_api_url = "http://" . SSH_IP . ":2121/";
        $post_data = array(
            "type" => "publish",
            "content" => $content,
            "to" => $to_uid,
        );
        //dump($post_data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $push_api_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Expect:"));
        $return = curl_exec($ch);
        curl_close($ch);
    }

    public function connectSSH($server, $port, $user, $password, $from)
    {
        $this->connection[$from->resourceId] = ssh2_connect($server, $port);
        if (ssh2_auth_pubkey_file($this->connection[$from->resourceId], $user, SITE_PATH . '/config/.ssh/id_rsa.pub', SITE_PATH . '/config/.ssh/id_rsa', 'bctos')) {
            return true;
        }elseif (ssh2_auth_password($this->connection[$from->resourceId], $user, $password)) {
            //$conn->send("Authentication Successful!\n");
            $this->shell[$from->resourceId] = ssh2_shell($this->connection[$from->resourceId], 'xterm', null, self::COLS, self::ROWS, SSH2_TERM_UNIT_CHARS);
            sleep(1);
            $this->conectado[$from->resourceId] = true;
            return true;
        } else {
            return false;
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->conectado[$conn->resourceId] = false;
        $this->clients->detach($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        $conn->close();
    }
}

?>
