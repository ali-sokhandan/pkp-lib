<?php
/**
 * @file classes/services/UserService.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2000-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserService
 * @ingroup services
 *
 * @brief Helper class that encapsulates author business logic
 */

namespace PKP\Services;

use \Application;
use \DBResultRange;
use \DAOResultFactory;
use \DAORegistry;
use \PKP\Services\EntityProperties\PKPBaseEntityPropertyService;

class UserService extends PKPBaseEntityPropertyService {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct($this);
	}

	/**
	 * Get users
	 *
	 * @param int $contextId
	 * @param array $args {
	 * 		@option string orderBy
	 * 		@option string orderDirection
	 * 		@option string roleIds
	 * 		@option int assignedToSubmission
	 * 		@option int assignedToSubmissionStage
	 * 		@option int assignedToSection
	 * 		@option string status
	 * 		@option string searchPhrase
	 * 		@option int count
	 * 		@option int offset
	 * }
	 *
	 * @return array
	 */
	public function getUsers($contextId, $args = array()) {
		$userListQB = $this->_buildGetUsersQueryObject($contextId, $args);
		$userListQO = $userListQB->get();
		$range = new DBResultRange($args['count'], null, $args['offset']);
		$userDao = DAORegistry::getDAO('UserDAO');
		$result = $userDao->retrieveRange($userListQO->toSql(), $userListQO->getBindings(), $range);
		$queryResults = new DAOResultFactory($result, $userDao, '_returnUserFromRowWithData');

		return $queryResults->toArray();
	}

	/**
	 * Get max count of users matching a query request
	 *
	 * @see self::getSubmissions()
	 * @return int
	 */
	public function getUsersMaxCount($contextId, $args = array()) {
		$userListQB = $this->_buildGetUsersQueryObject($contextId, $args);
		$countQO = $userListQB->countOnly()->get();
		$countRange = new DBResultRange($args['count'], 1);
		$userDao = DAORegistry::getDAO('UserDAO');
		$countResult = $userDao->retrieveRange($countQO->toSql(), $countQO->getBindings(), $countRange);
		$countQueryResults = new DAOResultFactory($countResult, $userDao, '_returnUserFromRowWithData');

		return (int) $countQueryResults->getCount();
	}

	/**
	 * Build the submission query object for getSubmissions requests
	 *
	 * @see self::getSubmissions()
	 * @return object Query object
	 */
	private function _buildGetUsersQueryObject($contextId, $args = array()) {

		$defaultArgs = array(
			'orderBy' => 'id',
			'orderDirection' => 'DESC',
			'roleIds' => null,
			'assignedToSubmission' => null,
			'assignedToSubmissionStage' => null,
			'assignedToSection' => null,
			'status' => 'active',
			'searchPhrase' => null,
			'count' => 20,
			'offset' => 0,
		);

		$args = array_merge($defaultArgs, $args);

		$userListQB = new QueryBuilders\UserListQueryBuilder($contextId);
		$userListQB
			->orderBy($args['orderBy'], $args['orderDirection'])
			->filterByRoleIds($args['roleIds'])
			->assignedToSubmission($args['assignedToSubmission'], $args['assignedToSubmissionStage'])
			->assignedToSection($args['assignedToSection'])
			->filterByStatus($args['status'])
			->searchPhrase($args['searchPhrase']);

		\HookRegistry::call('User::getUsers::queryBuilder', array($userListQB, $contextId, $args));

		return $userListQB;
	}

	/**
	 * Get a single user by ID
	 *
	 * @param $userId int
	 * @return User
	 */
	public function getUser($userId) {
		$userDao = DAORegistry::getDAO('UserDAO');
		$user = $userDao->getById($userId);
		return $user;
	}

	/**
	 * @copydoc \PKP\Services\EntityProperties\EntityPropertyInterface::getProperties()
	 */
	public function getProperties($user, $props, $args = null) {
		$request = $args['request'];
		$context = $request->getContext();

		$values = array();
		foreach ($props as $prop) {
			switch ($prop) {
				case 'id':
					$values[$prop] = (int) $user->getId();
					break;
				case 'userName':
					$values[$prop] = $user->getUserName();
					break;
				case 'fullName':
					$values[$prop] = $user->getFullName();
					break;
				case 'firstName':
					$values[$prop] = $user->getFirstName();
					break;
				case 'middleName':
					$values[$prop] = $user->getMiddleName();
					break;
				case 'lastName':
					$values[$prop] = $user->getLastName();
					break;
				case 'initials':
					$values[$prop] = $user->getInitials();
					break;
				case 'salutation':
					$values[$prop] = $user->getSalutation();
					break;
				case 'suffix':
					$values[$prop] = $user->getSuffix();
					break;
				case 'affiliation':
					$values[$prop] = $user->getAffiliation();
					break;
				case 'country':
					$values[$prop] = $user->getCountry();
					break;
				case 'url':
					$values[$prop] = $user->getUrl();
					break;
				case 'email':
					$values[$prop] = $user->getEmail();
					break;
				case 'orcid':
					$values[$prop] = $user->getOrcid(null);
					break;
				case 'biography':
					$values[$prop] = $user->getBiography();
					break;
				case 'signature':
					$values[$prop] = $user->getSignature();
					break;
				case 'authId':
					$values[$prop] = $user->getAuthId();
					break;
				case 'authString':
					$values[$prop] = $user->getAuthStr();
					break;
				case 'gender':
					$values[$prop] = $user->getGender();
					break;
				case 'phone':
					$values[$prop] = $user->getPhone();
					break;
				case 'mailingAddress':
					$values[$prop] = $user->getMailingAddress();
					break;
				case 'billingAddress':
					$values[$prop] = $user->getBillingAddress();
					break;
				case 'gossip':
					$values[$prop] = $user->getGossip();
					break;
				case 'disabled':
					$values[$prop] = (boolean) $user->getDisabled();
					break;
				case 'disabledReason':
					$values[$prop] = $user->getDisabledReason();
					break;
				case 'dateRegistered':
					$values[$prop] = $user->getDateRegistered();
					break;
				case 'dateValidated':
					$values[$prop] = $user->getDateValidated();
					break;
				case 'dateLastLogin':
					$values[$prop] = $user->getDateLastLogin();
					break;
				case 'mustChangePassword':
					$values[$prop] = (boolean) $user->getMustChangePassword();
					break;
				case '_href':
					$values[$prop] = null;
					if (!empty($args['slimRequest'])) {
						$route = $args['slimRequest']->getAttribute('route');
						$arguments = $route->getArguments();
						$values[$prop] = $this->getAPIHref(
							$args['request'],
							$arguments['contextPath'],
							$arguments['version'],
							'users',
							$user->getId()
						);
					}
					break;
				case 'groups':
					$values[$prop] = null;
					if ($context) {
						import('lib.pkp.classes.security.UserGroupDAO');
						$userGroupDao = DAORegistry::getDAO('UserGroupDAO');
						$userGroups = $userGroupDao->getByUserId($user->getId(), $context->getId());
						$values[$prop] = array();
						while ($userGroup = $userGroups->next()) {
							$values[$prop][] = array(
								'id' => (int) $userGroup->getId(),
								'name' => $userGroup->getName(null),
								'abbrev' => $userGroup->getAbbrev(null),
								'roleId' => (int) $userGroup->getRoleId(),
								'showTitle' => (boolean) $userGroup->getShowTitle(),
								'permitSelfRegistration' => (boolean) $userGroup->getPermitSelfRegistration(),
								'recommendOnly' => (boolean) $userGroup->getRecommendOnly(),
							);
						}
					}
					break;
				case 'interests':
					$values[$prop] = null;
					if ($context) {
						import('lib.pkp.classes.user.InterestDAO');
						$interestDao = DAORegistry::getDAO('InterestDAO');
						$interestEntryIds = $interestDao->getUserInterestIds($user->getId());
						if (!empty($interestEntryIds)) {
							import('lib.pkp.classes.user.InterestEntryDAO');
							$interestEntryDao = DAORegistry::getDAO('InterestEntryDAO');
							$results = $interestEntryDao->getByIds($interestEntryIds);
							$values[$prop] = array();
							while ($interest = $results->next()) {
								$values[$prop][] = array(
									'id' => (int) $interest->getId(),
									'interest' => $interest->getInterest(),
								);
							}
						}
					}
					break;
			}

			\HookRegistry::call('User::getProperties::values', array(&$values, $user, $props, $args));
		}

		return $values;
	}

	/**
	 * @copydoc \PKP\Services\EntityProperties\EntityPropertyInterface::getSummaryProperties()
	 */
	public function getSummaryProperties($user, $args = null) {
		$props = array (
			'id','_href','userName','email','fullName','orcid','groups','disabled',
		);

		\HookRegistry::call('User::getProperties::summaryProperties', array(&$props, $user, $args));

		return $this->getProperties($user, $props, $args);
	}

	/**
	 * @copydoc \PKP\Services\EntityProperties\EntityPropertyInterface::getFullProperties()
	 */
	public function getFullProperties($user, $args = null) {
		$props = array (
			'id','userName','fullName','firstName','middleName','lastName','initials','salutation',
			'suffix','affiliaton','country','email','url','orcid','groups','interests','biograpy','signature','authId',
			'authString','gender','phone','mailingAddress','billingAddress','gossip','disabled',
			'disabledReason','dateRegistered','dateValidated','dateLastLogin','mustChangePassword',
		);

		\HookRegistry::call('User::getProperties::fullProperties', array(&$props, $user, $args));

		return $this->getProperties($user, $props, $args);
	}
}
