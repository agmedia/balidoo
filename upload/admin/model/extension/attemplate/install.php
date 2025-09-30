<?php 

class ModelExtensionAttemplateInstall extends Model {  

	public function install($data=null) {

		$this->load->model('setting/setting');
		$this->load->model('user/user_group');
		
		$settings = array(
			'module_attemplate_status'		=> '1',
		);
			
		$this->model_setting_setting->editSetting('module_attemplate', $settings );		

		$this->model_user_user_group->addPermission($this->user->getId(), 'access', 'extension/module/attemplate');			
		$this->model_user_user_group->addPermission($this->user->getId(), 'modify', 'extension/module/attemplate');	
	}

	public function uninstall() {
		$this->load->model('setting/setting');
		$this->model_setting_setting->deleteSetting('module_attemplate');
	}	
}

?>