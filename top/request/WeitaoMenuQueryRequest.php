<?php
/**
 * TOP API: taobao.weitao.menu.query request
 * 
 * @author auto create
 * @since 1.0, 2014-07-03 16:46:54
 */
class WeitaoMenuQueryRequest
{
	
	private $apiParas = array();
	
	public function getApiMethodName()
	{
		return "taobao.weitao.menu.query";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
