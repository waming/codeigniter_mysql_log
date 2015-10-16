<?php

class My_Model extends CI_Model
{
	/* 选择当前连接的数据库配置  */
	protected $_db_name = 'default';

	/* 当前连接的主数据库 */
	public $_db_master = NULL;

	/* 当前连接的从数据库 */
	public $_db_slave  = NULL;

	public function __construct()
	{
		parent::__construct();

		if(!$this->_db_master && !$this->_db_slave) {
			$this->_db_slave = $this->_db_master = $this->load->database($this->_db_name, TRUE);
		}

		//加载缓存驱动
		//$this->load->driver('cache', array('adapter' => 'redis', 'backup' => 'file'));
	}

	/**
	 * 添加记录方法,此方法会自动过滤数组中不存在的字段值
	 * @param $table 表名
	 * @param $data 需要添加的数据
	 * @param $group 分组名称
	 * @return Int 
	*/
	protected function add($table = '', $data = array(), $group = 'admin')
	{
		$result = 0;
		if (empty($table) || empty($data)) {
			return $result;
		}

		$fields = $this->_db_master->list_fields($table);
		foreach ($data as $k => $v) {
			if (!in_array($k, $fields)) {
				unset($data[$k]);
			}
		}
		$query  = $this->_db_master->insert($table, $data);
		if ($query) {
			$result = $this->_db_master->insert_id();
		}

		/* 加入操作日志 */
		return $result;
	}

	/**
	 * 修改记录，此方法会自动过滤数组中不存在的字段值
	 * @param $table 表名
	 * @param $data 需要更新的数据
	 * @return Int
	*/
	protected function myUpdate($table = '', $data = array(), $where = '')
	{
		$result = 0;
		if (empty($table) || empty($data) || empty($where)) {
			return $result;
		}

		$fields = $this->_db_master->list_fields($table);
		foreach ($data as $k => $v) {
			if (!in_array($k, $fields)) {
				unset($data[$k]);
			}
		}

		$this->_db_master->where($where);
		$query = $this->_db_master->update($table, $data);

		if ($query) {
			$result = $this->_db_master->affected_rows();
		}
		return $result;
	}

	/* 将所有的SQL记录到日志中 */
	final private function _queriesToLog()
	{
		$salve_times = $this->_db_slave->query_times;
		if ($salve_times) {
			foreach ($this->_db_slave->queries as $k => $v) {
				$v   = str_replace("\n", ' ' , $v); 
				$sql = $v." 运行时间：".$salve_times[$k];
				log_message('error', $sql);
			}
		}

		/*$master_times = $this->_db_master->query_times;
		if ($master_times) {
			foreach ($this->_db_master->queries as $k => $v) {
				$v   = str_replace("\n", '' , $v);
				$sql = $v." 运行时间：".$master_times[$k];
				log_message('error', $sql);
			}
		}*/

		return false;
	}

	public function __destruct()
	{
		$this->_queriesToLog();

		if($this->_db_master){
			$this->_db_master->close();
		}
		$this->_db_slave->close();
	}
}
?>
