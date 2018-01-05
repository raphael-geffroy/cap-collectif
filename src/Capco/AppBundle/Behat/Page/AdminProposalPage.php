<?php

namespace Capco\AppBundle\Behat\Page;

use Capco\AppBundle\Behat\PageTrait;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class AdminProposalPage extends Page
{
    use PageTrait;

    protected $path = 'admin/capco/app/proposal/{proposalid}/edit';

    protected $elements = [
        'proposal content tab' => '#proposal-admin-page-tabs-tab-1',
        'proposal advancement tab' => '#proposal-admin-page-tabs-tab-2',
        'proposal actuality tab' => '#proposal-admin-page-tabs-tab-3',
        'proposal evaluation tab' => '#proposal-admin-page-tabs-tab-4',
        'proposal status tab' => '#proposal-admin-page-tabs-tab-5',
        'proposal title' => '#proposal_title',
        'proposal summary' => '#proposal_summary',
        'proposal save' => '#proposal_admin_content_save',
        'proposal advancement selection' => '#item_0 .form-group .react-toggle',
        'proposal advancement winner' => '#item_0 .form-group .react-toggle',
        'proposal advancement closed' => '#item_1 .form-group .react-toggle',
        'proposal advancement selection to come' => '#item_2 .form-group .react-toggle',
        'proposal advancement realisation to come' => '#item_3 .form-group .react-toggle',
        'proposal advancement selection status' => '#item_0 select',
        'proposal advancement save' => '#proposal_advancement_save',
    ];

    public function clickSaveContentProposalButton()
    {
        $this->getElement('proposal save')->click();
    }

    public function clickSaveProposalAdvancementButton()
    {
        $this->getElement('proposal advancement save')->click();
    }

    public function clickAdvancementTab()
    {
        $this->getElement('proposal advancement tab')->click();
    }

    public function getProposalElement(string $element)
    {
        return $this->getElement($element);
    }

    public function checkProposalCheckbox(string $element)
    {
        $this->getProposalElement($element)->click();
    }

    public function selectProposalAdvancementStatus(string $status, string $element)
    {
        return $this->getElement($element)->selectOption($status);
    }
}
