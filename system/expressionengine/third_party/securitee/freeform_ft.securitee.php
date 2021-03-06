<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
 * mithra62 - Securit:ee
 *
 * @package		mithra62:Securitee
 * @author		Eric Lamb
 * @copyright	Copyright (c) 2014, mithra62, Eric Lamb.
 * @link		http://mithra62.com/projects/view/securit-ee/
 * @version		1.4
 * @filesource 	./system/expressionengine/third_party/securitee/
 */

 /**
 * Securit:ee - Freeform Encryption Fieldtype
 *
 * Contains the Securit:ee Encrypter Fieldtype for Freeform
 *
 * @package 	mithra62:Securitee
 * @author		Eric Lamb
 * @filesource 	./system/expressionengine/third_party/securitee/freeform_ft.securitee.php
 */
class Securitee_freeform_ft extends Freeform_base_ft
{
	/**
	 * The Freeform Fieldtype details
	 * @var array
	 */
    public  $info   = array( 
        'name'          => 'Securit:ee - Encrypter', 
        'version'       => '1.4.3',
        'description'   => 'Allows for secure storage of your data.'
    );
    
    /**
     * The available form field types
     * @var array
     */
    public $field_types = array(
    		'password' => 'Password', 
    		'input' => 'Input', 
    		'textarea' => 'Textarea'
    );    
    
    public function __construct ()
    {
    	parent::__construct();
    
    	include PATH_THIRD.'securitee/config'.EXT;
    	$this->info['version'] = $config['version'];
    	$this->EE->load->library('encrypt');
    	$this->EE->load->add_package_path(PATH_THIRD.'securitee/');
    	$this->EE->load->model('securitee_settings_model', 'securitee_settings', TRUE);
    	$this->EE->load->library('encryption_ft');
    	$this->EE->encryption_ft->settings = $this->settings;
    	$this->EE->lang->loadfile('securitee');
    	$this->EE->load->model('field_model');   
		$this->EE->load->remove_package_path(); 	
    } 

    /**
     * Prepares the data for display through a front end template tag and returns it (if allowed)
     * @param array $data
     * @return string
     */
    public function replace_tag($data)
    {
    	if((isset($this->settings['decrypt_access']) && is_array($this->settings['decrypt_access']) && in_array($this->EE->session->userdata('group_id'), $this->settings['decrypt_access'])))
    	{
    		return $this->EE->encrypt->decode(htmlspecialchars_decode($data));
    	}
    
    	return $this->settings['hidden_text'];
    }
    
    public function export($data, $export_type)
    {
    	return $this->replace_tag($data);
    }

    /**
     * Prepares the data for display in the Control Panel and returns it (if allowed)
     * @param unknown $data
     * @return string
     */
    public function display_entry_cp($data)
    {
    	return $this->replace_tag($data);
    }

    public function display_field ($data)
    {
        return $this->EE->encryption_ft->display_field($data, $this->settings, $this->field_name);	
    }
    
    /**
     * Wrapper to format the data on submission
     * @param array $data
     * @return array
     */
    public function save($data)
    {
    	return $this->EE->encryption_ft->save_field($data, $this->settings, $this->field_name);
    }    
    
    /**
     * Method to save the settings on POST
     * @return array
     */
    public function save_settings()
    {
    	return array(
    			'decrypt_access'		=> $this->EE->input->post('decrypt_access'),
    			'field_max_length'		=> $this->EE->input->post('field_max_length'),
    			'display_field_type'	=> $this->EE->input->post('display_field_type'),
    			'hidden_text'			=> $this->EE->input->post('hidden_text')
    	);
    }    
    
    /**
     * Displays the settings for the FieldType
     * @param array $data
     */
    public function display_settings($data)
    {
    	$selected = (empty($data['display_field_type']) ? FALSE : $data['display_field_type']);
    	$this->EE->table->add_row(
    			'<strong>'.lang('display_type').'</strong><div class="subtext">'.lang('display_type_instructions').'</div>',
    			form_dropdown('display_field_type', $this->field_types, $selected)
    	);
    
    	$field_max_length = (empty($data['field_max_length']) ? 128 : $data['field_max_length']);
    	$this->EE->table->add_row(
    			'<strong>'.lang('field_max_length').'</strong>',
    			form_input(array('id'=>'field_max_length','name'=>'field_max_length', 'size'=>4,'value'=>$field_max_length))
    	);
    
    	$member_groups = $this->EE->securitee_settings->get_member_groups();
    	$selected = (empty($data['decrypt_access']) ? $data['decrypt_access'] : '******');
    	$this->EE->table->add_row(
    			'<strong>'.lang('decrypt_access').'</strong><div class="subtext">'.lang('decrypt_access_instructions').'</div>',
    			form_multiselect('decrypt_access[]', $member_groups, $selected)
    	);
    
    	$hidden_text = (empty($data['hidden_text']) ? '******' : $data['hidden_text']);
    	$this->EE->table->add_row(
    			'<strong>'.lang('hidden_text').'</strong><div class="subtext">'.lang('hidden_text_instructions').'</div>',
    			form_input(array('id'=>'hidden_text','name'=>'hidden_text', 'size'=>4,'value'=>$hidden_text))
    	);
    }    
}