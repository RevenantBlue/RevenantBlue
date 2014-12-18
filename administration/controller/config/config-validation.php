<?php
namespace RevenantBlue\Admin;

class ConfigValidation extends GlobalValidation {

	public  $errors = array();            // Contains an array that stores all errors that occur during validation.

}

class TaskValidation extends GlobalValidation {

	public $errors = array();
	public $task;
	public $id;
	public $name;
	public $alias;
	public $description;
	public $command;
	public $minutes;
	public $hours;
	public $daysOfMonth;
	public $months;
	public $daysOfWeek;
	public $years;
	public $log;
	private $config;

	public function __construct($task) {

		$this->config = new Config;
		
		if(!empty($task['id'])) {
			$this->task['id'] = $this->id = $task['id'];
		}
		$this->task['name'] = $this->validateString($task['name'], 255, 'The task name cannot be longer than 255 characters.');
		$this->task['name'] = $this->validateRequired($this->task['name'], 'The name field is required.');
		$this->task['alias'] = $this->validateAlias($task['name'], TRUE);
		$this->task['description'] = $task['description'];
		$this->task['command'] = $this->validateRequired($task['command'], 'The command field is required.');
		$this->task['minutes'] = $this->validateRequired($task['minutes'], 'The minutes field is required');
		$this->task['hours'] = $this->validateRequired($task['hours'], 'The hours field is required');
		$this->task['daysOfMonth'] = $this->validateRequired($task['daysOfMonth'], 'The days of month field is required');
		$this->task['months'] = $this->validateRequired($task['months'], 'The months field is required');
		$this->task['daysOfWeek'] = $this->validateRequired($task['daysOfWeek'], 'The days of week field is required');
		$this->task['years'] = $this->validateRequired($task['years'], 'The years field is required');
		$this->task['log'] = $task['log'];
	}

	protected function checkForDuplicateAlias($alias) {
		$task = $this->config->getTaskByAlias($alias);

		if(!empty($task['id'])) {
			if(!empty($this->id) && (int)$this->id === (int)$task['id']) {
				return FALSE;
			} else {
				return TRUE;
			}
		} else {
			return FALSE;
		}
	}
}
