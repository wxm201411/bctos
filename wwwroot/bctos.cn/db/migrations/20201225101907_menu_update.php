<?php

use Phinx\Migration\AbstractMigration;

final class MenuUpdate extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $this->execute('update wp_menu set is_hide=0 where id=1');
        $this->execute("update wp_menu set pid=0 icon='layui-icon layui-icon-download-circle' where id=888");
    }
}
