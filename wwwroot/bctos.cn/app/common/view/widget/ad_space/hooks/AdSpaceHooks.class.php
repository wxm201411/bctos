<?php
/**
 * 广告位钩子
 * @author zivss <guolee226@gmail.com>
 * @version TS3.0
 */
class AdSpaceHooks extends Hooks
{
	/**
	 * 显示广告位钩子
	 * @param array $param 钩子相关参数
	 * @return void
	 */
	public function show_ad_space($param)
	{
		// 获取位置广告信息
		$place = safe($param['place']);
		$placeInfo = $this->_getPlaceKey($place);
		$data = $this->model('AdSpace')->getAdSpaceByPlace($placeInfo['id']);
		foreach($data as &$value) {
			if($value['display_type'] == 3) {
				$value['content'] = unserialize($value['content']);
				// 获取附件图片地址
				foreach($value['content'] as &$val) {
					$attachInfo = model('Attach')->getAttachById($val['banner']);
					if($placeInfo['width'] && $placeInfo['height']){
						$val['bannerpic'] = getImageUrl($attachInfo['save_path'].$attachInfo['save_name'],$placeInfo['width'],$placeInfo['height']);
					}else{
						$val['bannerpic'] = getImageUrl($attachInfo['save_path'].$attachInfo['save_name']);
					}
				}
			}
		}
		$this->assign('data', $data);
		// 设置宽度
		$width = intval($placeInfo['width']);
		$this->assign('width', $width);
		// 设置高度
		$height = intval($placeInfo['height']);
		$this->assign('height', $height);
		// 设置距离顶端距离
		$top = intval($placeInfo['top']);
        $bottom = intval($placeInfo['bottom']);
		$this->assign('top', $top);
        $this->assign('bottom', $bottom);
		//增加自定义广告显示模板功能
		$tpl = $placeInfo['tpl'] ? $placeInfo['tpl'] : 'show_ad_space';
		$tpl = $param['tpl']?$param['tpl']:$tpl;
		return $this->fetch($tpl);
	}

	/**
	 * 广告位插件
	 * @return void
	 */
	public function config()
	{
		// 位置数组
		$placeArr = $this->_getPlaceData();
		$placeArray = [];
		foreach($placeArr as $value) {
			$placeArray[$value['id']] = $value['name'];
		}
		$this->assign('place_array', $placeArray);
		// 列表数据
		$list = $this->model('AdSpace')->getAdSpaceList();
		$this->assign('list', $list);

		return $this->fetch('config');
	}

	/**
	 * 添加广告位页面
	 * @return void
	 */
	public function addAdSpace()
	{
		// 位置数组
		$placeArr = $this->_getPlaceData();
		$this->assign('placeArr', $placeArr);
		// 是否可编辑
		$this->assign('editPage', false);

		$previewUrl = Addons::createAddonUrl('AdSpace', 'previewPic');
		$this->assign('previewUrl', $previewUrl);

		return $this->fetch('show_ad_space');
	}

	/**
	 * 添加广告位操作
	 * @return void
	 */
	public function doAddAdSpace()
	{
		// 组装数据
		$data['title'] = safe($_POST['title']);
		$data['place'] = intval($_POST['place']);
		$data['is_active'] = intval($_POST['is_active']);
		$data['ctime'] = time();
		$data['display_type'] = intval($_POST['display_type']);
		switch($data['display_type']) {
			case 1:
				$data['content'] = $_POST['html_form'];
				break;
			case 2:
				$data['content'] = $_POST['code_form'];
				break;
			case 3:
				$picData = [];
				for($i = 0; $i < count($_POST['banner']); $i++) {
					$picData[] = array('banner'=>$_POST['banner'][$i], 'bannerurl'=>$_POST['bannerurl'][$i]);
				}
				$data['content'] = serialize($picData);
				break;
		}
		$res = $this->model('AdSpace')->doAddAdSpace($data);

		return false;
	}

	/**
	 * 删除广告位操作
	 * @return json 是否删除成功
	 */
	public function doDelAdSpace()
	{
		$result = [];
		$ids = safe($_POST['ids']);
		if(empty($ids)) {
			$result['status'] = 0;
			$result['info'] = '参数不能为空';
			exit(json_encode($result));
		}
		$res = $this->model('AdSpace')->doDelAdSpace($ids);
		if($res) {
			$result['status'] = 1;
			$result['info'] = '删除成功';
		} else {
			$result['status'] = 0;
			$result['info'] = '删除失败';
		}
		exit(json_encode($result));
	}

	/**
	 * 编辑广告位页面
	 * @return void
	 */
	public function editAdSpace()
	{
		// 位置数组
		$placeArr = $this->_getPlaceData();
		$this->assign('placeArr', $placeArr);
		// 获取广告位信息
		$id = intval(I('id'));
		$data = $this->model('AdSpace')->getAdSpace($id);
		// 轮播图片内容解析
		if($data['display_type'] == 3) {
			$data['content'] = unserialize($data['content']);
			foreach($data['content'] as &$value) {
				$attachInfo = model('Attach')->getAttachById($value['banner']);
				$value['bannerpic'] = getImageUrl($attachInfo['save_path'].$attachInfo['save_name']);
			}
		}
		$this->assign('data', $data);
		$this->assign('editPage', true);
		$placeInfo = $this->_getPlaceByID($data['place']);
		$this->assign('placeInfo', $placeInfo);
		$previewUrl = Addons::createAddonUrl('AdSpace', 'previewPic');
		$this->assign('previewUrl', $previewUrl);

		return $this->fetch('add_ad_space');
	}

	/**
	 * 编辑广告位操作
	 * @return void
	 */
	public function doEditAdSpace()
	{
		// 数据组装
		$id = intval($_POST['ad_id']);
		$data['title'] = safe($_POST['title']);
		$data['place'] = intval($_POST['place']);
		$data['is_active'] = intval($_POST['is_active']);
		$data['mtime'] = time();
		$data['display_type'] = intval($_POST['display_type']);
		switch($data['display_type']) {
			case 1:
				$data['content'] = $_POST['html_form'];
				break;
			case 2:
				$data['content'] = $_POST['code_form'];
				break;
			case 3:
				$picData = [];
				for($i = 0; $i < count($_POST['banner']); $i++) {
					$picData[] = array('banner'=>$_POST['banner'][$i], 'bannerurl'=>$_POST['bannerurl'][$i]);
				}
				$data['content'] = serialize($picData);
				break;
		}
		$res = $this->model('AdSpace')->doEditAdSpace($id, $data);

		return false;
	}

	/**
	 * 移动广告位操作
	 * @return void
	 */
	public function doMvAdSpace()
	{
		$result = [];
		$id = intval($_POST['id']);
		$baseId = intval($_POST['baseId']);
		if($id <= 0 || $baseId <= 0) {
			$result['status'] = 0;
			$result['info'] = '参数错误';
			exit(json_encode($result));
		}
		$res = $this->model('AdSpace')->doMvAdSpace($id, $baseId);
		if($res) {
			$result['status'] = 1;
			$result['info'] = '操作成功';
		} else {
			$result['status'] = 0;
			$result['info'] = '操作失败';
		}

		exit(json_encode($result));
	}

	/**
	 * 获取广告位配置信息
	 * @return array 广告位配置信息
	 */
	private function _getPlaceData()
	{
		$data = include(ADDON_PUBLIC_PATH.'/plugin/AdSpace/config/config.php');
		return $data;
	}

	/**
	 * 通过键值获取相应的ID
	 * @return integer 对应键值的ID
	 */
	private function _getPlaceKey($key)
	{
		$data = $this->_getPlaceData();
		return $data[$key];
	}

	/**
	 * 通过ID获取相应的广告位信息
	 * @return array 对应的广告位信息
	 */
	private function _getPlaceByID($id)
	{
		$data = $this->_getPlaceData();
		foreach($data as $k=>$v){
			if($v['id'] != $id){
				continue;
			}
			return $v;
		}
		return [];
	}

	public function previewPic() {
		$params = safe(I('params'));
		$params = explode(',', $params);
		$content = [];
		foreach ($params as $key => &$val) {
			$attachInfo = model('Attach')->getAttachById($val);
			$tmp['bannerpic'] = getImageUrl($attachInfo['save_path'].$attachInfo['save_name']);
			$content[] = $tmp;
			unset($tmp);
		}
		$this->assign('content', $content);
		$this->assign('width',safe(I('width')));
		//自定义预览模板
		$previewTPL = safe(I('preview')) ?safe(I('preview')) : 'preview_ad';
		//$previewTPL = 'previewAd';
		return $this->fetch($previewTPL);
	}
}