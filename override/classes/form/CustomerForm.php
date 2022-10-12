<?php
use PrestaShop\PrestaShop\Core\Util\InternationalizedDomainNameConverter;
use Symfony\Component\Translation\TranslatorInterface;
class CustomerForm extends CustomerFormCore
{
    private $context;
    private $urls;

    private $customerPersister;
    private $guest_allowed;
    private $passwordRequired = true;

    private $IDNConverter;

    public function __construct(
        Smarty $smarty,
        Context $context,
        TranslatorInterface $translator,
        CustomerFormatter $formatter,
        CustomerPersister $customerPersister,
        array $urls
    ) {
        parent::__construct(
            $smarty,
            $context,
            $translator,
            $formatter,
            $customerPersister,
            $urls
        );
        $this->context = $context;
        $this->urls = $urls;
        $this->customerPersister = $customerPersister;
        $this->IDNConverter = new InternationalizedDomainNameConverter();
    }
    public function validate()
    {
        $object_dir = _PS_OVERRIDE_DIR_.'classes/Validate.php';
        require_once ($object_dir);
        $emailField = $this->getField('email');
        $id_customer = Customer::customerExists($emailField->getValue(), true, true);
        $customer = $this->getCustomer();
        if ($id_customer && $id_customer != $customer->id) {
            $emailField->addError($this->translator->trans(
                'The email is already used, please choose another one or sign in',
                [],
                'Shop.Notifications.Error'
            ));
        }
        $birthdayField = $this->getField('birthday');
        if (!empty($birthdayField) &&
            !empty($birthdayField->getValue()) &&
            Validate::isBirthDate($birthdayField->getValue(), $this->context->language->date_format_lite)
        ) {
            $dateBuilt = DateTime::createFromFormat(
                $this->context->language->date_format_lite,
                $birthdayField->getValue()
            );
            $birthdayField->setValue($dateBuilt->format('Y-m-d'));
        }
        $passwordField = $this->getField('password');
        if ((!empty($passwordField->getValue()) || $this->passwordRequired)
            && Validate::isPasswd($passwordField->getValue()) === false) {
            $passwordField->addError($this->translator->trans(
                'Password must be between 5 and 72 characters long',
                [],
                'Shop.Notifications.Error'
            ));
        }
        $this->validateFieldsLengths();
        $this->validateByModules();
        $spamCheckResult = Validate::spamCheckUser($customer->firstname.' '.$customer->lastname, $customer->email);
        if ($spamCheckResult->allow == 0)
        $emailField->addError($this->translator->trans(
            $spamCheckResult->comment, array(), 'Shop.Notifications.Error'
        ));
        return parent::validate();
    }
    private function validateByModules()
    {
        $formFieldsAssociated = [];
        // Group FormField instances by module name
        foreach ($this->formFields as $formField) {
            if (!empty($formField->moduleName)) {
                $formFieldsAssociated[$formField->moduleName][] = $formField;
            }
        }
        // Because of security reasons (i.e password), we don't send all
        // the values to the module but only the ones it created
        foreach ($formFieldsAssociated as $moduleName => $formFields) {
            if ($moduleId = Module::getModuleIdByName($moduleName)) {
                // ToDo : replace Hook::exec with HookFinder, because we expect a specific class here
                $validatedCustomerFormFields = Hook::exec('validateCustomerFormFields', ['fields' => $formFields], $moduleId, true);

                if (is_array($validatedCustomerFormFields)) {
                    array_merge($this->formFields, $validatedCustomerFormFields);
                }
            }
        }
    }
}