<?php
namespace TYPO3\PhoenixDemoTypo3Org\Controller;

/*                                                                        *
 * This script belongs to the FLOW3 package "PhoenixDemoTypo3Org".        *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License as published by the Free   *
 * Software Foundation, either version 3 of the License, or (at your      *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        *
 * You should have received a copy of the GNU General Public License      *
 * along with the script.                                                 *
 * If not, see http://www.gnu.org/licenses/gpl.html                       *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Controller that handles the creation of temporary Accounts
 *
 */
class RegistrationController extends \TYPO3\FLOW3\MVC\Controller\ActionController {

	/**
	 * @inject
	 * @var \TYPO3\FLOW3\Security\AccountRepository
	 */
	protected $accountRepository;

	/**
	 * @inject
	 * @var \TYPO3\FLOW3\Security\AccountFactory
	 */
	protected $accountFactory;

	/**
	 * @return string
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function indexAction() {
	}

	/**
	 * Displays a form that creates a temporary account
	 *
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function newAccountAction() {
		$number = (time() - 1302876012);
		$registration = new \TYPO3\PhoenixDemoTypo3Org\Domain\Model\Registration();
		$registration->setFirstName('John');
		$registration->setLastName('Doe');
		$registration->setUsername('demo' . $number);
		$registration->setPassword('demo');

		$this->view->assign('registration', $registration);
	}

	/**
	 * Action for creating a temporary account
	 *
	 * @param \TYPO3\PhoenixDemoTypo3Org\Domain\Model\Registration $registration
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function createAccountAction(\TYPO3\PhoenixDemoTypo3Org\Domain\Model\Registration $registration) {
		$accountIdentifier = $registration->getUsername();
		$existingAccount = $this->accountRepository->findActiveByAccountIdentifierAndAuthenticationProviderName($accountIdentifier, 'DefaultProvider');
		if ($existingAccount !== NULL) {
			$this->flashMessageContainer->add('An account with the username "' . $accountIdentifier . '" already exists.');
			$this->forward('newAccount');
		}

		$account = $this->createTemporaryAccount($accountIdentifier, $registration->getPassword(), $registration->getFirstName(), $registration->getLastName());
		$this->accountRepository->add($account);

		$uriBuilder = new \TYPO3\FLOW3\MVC\Web\Routing\UriBuilder();
		$uriBuilder->setRequest($this->request->getParentRequest());
		$redirectUri = $uriBuilder
			->setCreateAbsoluteUri(TRUE)
			->uriFor('index', array('username' => $accountIdentifier), 'Login', 'TYPO3.TYPO3');
		$this->redirectToUri($redirectUri);
	}

	/**
	 * Creates a temporary account
	 *
	 * @param string $accountIdentifier
	 * @param string $password
	 * @param string $firstName
	 * @param string $lastName
	 * @return \TYPO3\FLOW3\Security\Account
	 */
	protected function createTemporaryAccount($accountIdentifier, $password, $firstName, $lastName) {
		if (strlen($firstName) === 0 && strlen($lastName) === 0) {
			$firstName = 'Santa';
			$lastName = 'Claus';
		}
		$name = new \TYPO3\Party\Domain\Model\PersonName('', $firstName, '', $lastName);
		$user = new \TYPO3\TYPO3\Domain\Model\User();
		$user->getPreferences()->set('context.workspace', 'user-' . $accountIdentifier);
		$user->setName($name);
		$account = $this->accountFactory->createAccountWithPassword($accountIdentifier, $password, array('Administrator'));
		$account->setParty($user);
		$account->setExpirationDate(new \DateTime('+1 week'));
		return $account;
	}

	/**
	 * Confirmation for createAccountAction
	 *
	 * @param string $username Identifier of the account that was created
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function createAccountConfirmationAction($username) {
		$this->view->assign('username', $username);
	}

	/**
	 * @return string|boolean The flash message or FALSE if no flash message should be set
	 */
	protected function getErrorFlashMessage() {
		return FALSE;
	}
}
?>
