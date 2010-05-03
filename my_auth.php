<?php
/* SVN FILE: $Id$ */

/**
 * Authentication component
 *
 * Manages user logins and permissions.
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) :  Rapid Development Framework (http://www.cakephp.org)
 * Copyright 2005-2010, Cake Software Foundation, Inc. (http://www.cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright     Copyright 2005-2010, Cake Software Foundation, Inc. (http://www.cakefoundation.org)
 * @link          http://www.cakefoundation.org/projects/info/cakephp CakePHP(tm) Project
 * @package       cake
 * @subpackage    cake.cake.libs.controller.components
 * @since         CakePHP(tm) v 0.10.0.1076
 * @version       $Revision$
 * @modifiedby    $LastChangedBy$
 * @lastmodified  $Date$
 * @license       http://www.opensource.org/licenses/mit-license.php The MIT License
 */

App::import('Component', 'Auth');

/**
 * Extended Authentication control component class
 *
 * adds ability to include more models in auth returned data.
 *
 * @package       cake
 * @subpackage    cake.cake.libs.controller.components
 */
class CosAuthComponent extends AuthComponent {



/**
 * Array of models to include in the Auth data
 *
 */
 
 var $included = array();


/**
 * Identifies a user based on specific criteria.
 *
 * @param mixed $user Optional. The identity of the user to be validated.
 *              Uses the current user session if none specified.
 * @param array $conditions Optional. Additional conditions to a find.
 * @return array User record data, or null, if the user could not be identified.
 * @access public
 */
	function identify($user = null, $conditions = null) {
	   
		if ($conditions === false) {
			$conditions = null;
		} elseif (is_array($conditions)) {
			$conditions = array_merge((array)$this->userScope, $conditions);
		} else {
			$conditions = $this->userScope;
		}
		if (empty($user)) {
			$user = $this->user();
			if (empty($user)) {
				return null;
			}
		} elseif (is_object($user) && is_a($user, 'Model')) {
			if (!$user->exists()) {
				return null;
			}
			$user = $user->read();
			$user = $user[$this->userModel];
		} elseif (is_array($user) && isset($user[$this->userModel])) {
			$user = $user[$this->userModel];
		}
		if (is_array($user) && (isset($user[$this->fields['username']]) || isset($user[$this->userModel . '.' . $this->fields['username']]))) {

			if (isset($user[$this->fields['username']]) && !empty($user[$this->fields['username']])  && !empty($user[$this->fields['password']])) {
				if (trim($user[$this->fields['username']]) == '=' || trim($user[$this->fields['password']]) == '=') {
					return false;
				}
				$find = array(
					$this->userModel.'.'.$this->fields['username'] => $user[$this->fields['username']],
					$this->userModel.'.'.$this->fields['password'] => $user[$this->fields['password']]
				);
			} elseif (isset($user[$this->userModel . '.' . $this->fields['username']]) && !empty($user[$this->userModel . '.' . $this->fields['username']])) {
				if (trim($user[$this->userModel . '.' . $this->fields['username']]) == '=' || trim($user[$this->userModel . '.' . $this->fields['password']]) == '=') {
					return false;
				}
				$find = array(
					$this->userModel.'.'.$this->fields['username'] => $user[$this->userModel . '.' . $this->fields['username']],
					$this->userModel.'.'.$this->fields['password'] => $user[$this->userModel . '.' . $this->fields['password']]
				);
			} else {
				return false;
			}
			$model =& $this->getModel();
			$data = $model->find('first', array('conditions' => array_merge($find, $conditions), 'contain' => $this->included));
			if (empty($data) || empty($data[$this->userModel])) {
				return null;
			}
		} elseif (!empty($user) && is_string($user)) {
			$model =& $this->getModel();
			$data = $model->find(array_merge(array($model->escapeField() => $user), $conditions));

			if (empty($data) || empty($data[$this->userModel])) {
				return null;
			}
		}
		if (!empty($data)) {
			if (!empty($data[$this->userModel][$this->fields['password']])) {
				unset($data[$this->userModel][$this->fields['password']]);
			}
			return $data;
		}
		return null;
	}
}
?>