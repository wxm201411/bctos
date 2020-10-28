<?php
namespace app\common\model;
use app\common\model\Base;
/**
 * 搜索模型 - 业务逻辑模型
 * @author jason <yangjs17@yeah.net> 
 * @version TS3.0
 */
class Sphinx extends Base {

	private $table = DB_PREFIX. 'user';
	private $host = 'dev.zhishisoft.com';
	private $port =	9306;
	private $sdb;		// 实例

	/**
	 * 初始化方法，链接搜索引擎数据库
	 * sphinx1.10开始支持mysql协议
	 */
	public function __construct(){
		$connection	= array(
						'dbms'=>'mysql',
						'hostname'=>(config('SEARCHD_HOST') ? config('SEARCHD_HOST') : $this->host),
						'hostport'=>(config('SEARCHD_PORT') ? config('SEARCHD_PORT') : $this->port)
					  );
		$this->sdb = new Db($connection);
	}

	/**
	 * 重置数据库链接
	 * @param string $host 主机地址IP
	 * @param integer $port 端口号
	 * @return object 搜索模型对象
	 */
	public function connect($host, $port = 9306) {
		$connection	= array(
							'dbms'=>'mysql',
							'hostname'=>$host,
							'hostport'=>$port
					  );
		$this->sdb = new Db($connection);
		return $this;
	}
	
	/**
	 * 直接搜索sphinx，结果未处理
	 * @param string $query SQL查询语句
	 * @return array 查询出的相应结果
	 */
	public function query($query) {
		$list = M()::query($query);
		return $list;
	}
	
	/**
	 * 执行搜素，结果有处理
	 * @param string $query SQL查询语句
	 * @param integer $limit 结果集数目，默认为20
	 * @return array 查询出的相应结果
	 */
	public function search($query, $limit = 20) {
		// 搜索值为空，返回结果
		if(empty($query)) {
			return false;
		}
		
		$query .=" limit ".$this->getLimit($limit);		// limit处理
		$datas	=	M()::query($query);			// 执行SphinxQL查询

		if(!$datas) {
			return false;
		}
		// 获取关键词信息
		$metas = M()::query("SHOW META");
		if(!$metas) {
			return false;
		}
		// 处理数据
		foreach($metas as $v) {
			if($v['Variable_name'] == 'total_found') {
				$data['count'] = $v['Value'];
			}
			if($v['Variable_name'] == 'time') {
				$data['time'] = $v['Value'];
			}
			if(is_numeric($k = str_replace(array('keyword','[',']'), '', $v['Variable_name']))) {
				$data['matchwords'][$k]['keyword'] = $v['Value'];
				$data['keywords'][] = $v['Value'];
			}
			if(is_numeric($k = str_replace(array('docs','[',']'), '', $v['Variable_name']))) {
				$data['matchwords'][$k]['docs'] = $v['Value'];
			}
			if(is_numeric($k = str_replace(array('hits','[',']'), '', $v['Variable_name']))) {
				$data['matchwords'][$k]['hits'] = $v['Value'];
			}
		}
		$p = new Page($data['count'], $limit);
		$data['totalPages'] = $p->totalPages;
		$data['html'] = $p->show();
		$data['data'] = $datas;

		return $data;
	}

	/**
	 * 获取分页数，默认为1
	 * @return integer 分页数
	 */
	public function getPage() {
		return !empty(I(config('VAR_PAGE'))) && (I(config('VAR_PAGE')) > 0) ? intval(I(config('VAR_PAGE'))) : 1;
	}

	/**
	 * 获取limit查询条件
	 * @param integer $limit 结果集数目，默认为20
	 * @return string limit查询条件
	 */
	public function getLimit($limit = 20) {
		$nowPage = $this->getPage();
		$now = intval(abs($nowPage - 1) * $limit);
		return	$now.','.$limit;
	}
}