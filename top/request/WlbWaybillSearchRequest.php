<?php
/**
 * TOP API: taobao.wlb.waybill.search request
 * 
 * @author auto create
 * @since 1.0, 2014-07-03 16:46:54
 */
class WlbWaybillSearchRequest
{
	/** 
	 * 物流服务商ID
	 **/
	private $cpCode;
	
	private $apiParas = array();
	
	public function setCpCode($cpCode)
	{
		$this->cpCode = $cpCode;
		$this->apiParas["cp_code"] = $cpCode;
	}

	public function getCpCode()
	{
		return $this->cpCode;
	}

	public function getApiMethodName()
	{
		return "taobao.wlb.waybill.search";
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
