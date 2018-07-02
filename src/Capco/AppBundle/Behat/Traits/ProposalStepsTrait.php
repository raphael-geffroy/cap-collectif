<?php

namespace Capco\AppBundle\Behat\Traits;

use Behat\Gherkin\Node\TableNode;
use FilesystemIterator;

trait ProposalStepsTrait
{
    protected static $collectStepOpenParams = [
        'projectSlug' => 'budget-participatif-rennes',
        'stepSlug' => 'collecte-des-propositions',
    ];
    protected static $collectStepNotifiable = [
        'projectSlug' => 'budget-participatif-rennes',
        'stepSlug' => 'collecte-des-propositions',
    ];
    protected static $collectStepOpenPrivateParams = [
        'projectSlug' => 'budget-participatif-rennes',
        'stepSlug' => 'collecte-des-propositions-privee',
    ];
    protected static $collectStepClosedParams = [
        'projectSlug' => 'budget-participatif-rennes',
        'stepSlug' => 'collecte-des-propositions-fermee',
    ];
    protected static $selectionStepOpenParams = [
        'projectSlug' => 'budget-participatif-rennes',
        'stepSlug' => 'selection',
    ];
    protected static $votesDetailsPageParams = [
        'projectSlug' => 'budget-participatif-rennes',
    ];
    protected static $selectionStepWithSimpleVoteParams = [
        'projectSlug' => 'budget-participatif-rennes',
        'stepSlug' => 'selection',
    ];
    protected static $selectionStepWithBudgetVoteParams = [
        'projectSlug' => 'depot-avec-selection-vote-budget',
        'stepSlug' => 'selection-avec-vote-selon-le-budget',
    ];
    protected static $selectionStepWithBudgetVoteLimitedParams = [
        'projectSlug' => 'budget-avec-vote-limite',
        'stepSlug' => 'selection-avec-vote-budget-limite',
    ];
    protected static $selectionStepNotYetOpen = [
        'projectSlug' => 'budget-participatif-rennes',
        'stepSlug' => 'selection-a-venir',
    ];
    protected static $selectionStepClosed = [
        'projectSlug' => 'budget-participatif-rennes',
        'stepSlug' => 'fermee',
    ];
    protected static $proposalWithSimpleVoteParams = [
        'projectSlug' => 'budget-participatif-rennes',
        'stepSlug' => 'collecte-des-propositions',
        'proposalSlug' => 'renovation-du-gymnase',
    ];
    protected static $proposalByMSantoStefano = [
        'projectSlug' => 'budget-participatif-rennes',
        'stepSlug' => 'collecte-des-propositions',
        'proposalSlug' => 'ravalement-de-la-facade-de-la-bibliotheque-municipale',
    ];
    protected static $proposalsByUserTest = [
        'projectSlug' => 'budget-participatif-rennes',
        'stepSlug' => 'collecte-des-propositions',
        'proposalSlug' => 'renovation-du-gymnase',
    ];
    protected static $proposalCommentNotNotifiable = [
        'projectSlug' => 'appel-a-projets',
        'stepSlug' => 'collecte-des-propositions-avec-vote-simple',
        'proposalSlug' => 'mon-super-projet',
    ];
    protected static $proposalCommentNotifiable = [
        'projectSlug' => 'budget-participatif-rennes',
        'stepSlug' => 'collecte-des-propositions',
        'proposalSlug' => 'renovation-du-gymnase',
    ];
    protected static $proposalNotifiable = [
        'projectSlug' => 'budget-participatif-rennes',
        'stepSlug' => 'collecte-des-propositions',
        'proposalSlug' => 'renovation-du-gymnase',
    ];
    protected static $proposalNotifiableOfMine = [
        'projectSlug' => 'budget-participatif-rennes',
        'stepSlug' => 'collecte-des-propositions',
        'proposalSlug' => 'renovation-du-gymnase',
    ];
    protected static $proposalNotNotifiable = [
        'projectSlug' => 'budget-avec-vote-limite',
        'stepSlug' => 'collecte-avec-vote-simple-limite',
        'proposalSlug' => 'proposition-17',
    ];
    protected static $proposalWithBudgetVoteParams = [
        'projectSlug' => 'depot-avec-selection-vote-budget',
        'stepSlug' => 'collecte-des-propositions-1',
        'proposalSlug' => 'proposition-pas-chere',
    ];
    protected static $proposalNotYetVotable = [
        'projectSlug' => 'budget-participatif-rennes',
        'stepSlug' => 'collecte-des-propositions',
        'proposalSlug' => 'proposition-pas-encore-votable',
    ];
    protected static $proposalNotVotableAnymore = [
        'projectSlug' => 'budget-participatif-rennes',
        'stepSlug' => 'collecte-des-propositions',
        'proposalSlug' => 'proposition-plus-votable',
    ];

    protected static $proposalWithOneFollower = [
        'projectSlug' => 'budget-participatif-rennes',
        'stepSlug' => 'collecte-des-propositions',
        'proposalSlug' => 'installation-de-bancs-sur-la-place-de-la-mairie',
    ];

    /** @var array */
    protected $currentCollectsStep = [];

    // ********************************* Proposals *********************************************

    /**
     * @When I go to an open collect step
     */
    public function iGoToAnOpenCollectStep()
    {
        $this->visitPageWithParams('collect page', self::$collectStepOpenParams);
        $this->getSession()->wait(3000, "$('#proposal__step-page-rendered').length > 0");
    }

    /**
     * @When I go to a notifiable open collect step
     */
    public function iGoToANotifiableOpenCollectStep()
    {
        $this->visitPageWithParams('collect page', self::$collectStepNotifiable);
        $this->getSession()->wait(3000, "$('#proposal__step-page-rendered').length > 0");
    }

    /**
     * @When I go to a selection step
     */
    public function iGoToASelectionStep()
    {
        $this->visitPageWithParams('selection page', self::$selectionStepOpenParams);
        $this->getSession()->wait(3000, "$('#proposal__step-page-rendered').length > 0");
    }

    /**
     * @When I go to a private open collect step
     */
    public function iGoToAPrivateOpenCollectStep()
    {
        $this->visitPageWithParams('collect page', self::$collectStepOpenPrivateParams);
        $this->getSession()->wait(3000, "$('#proposal__step-page-rendered').length > 0");
    }

    /**
     * @When I go to a closed collect step
     */
    public function iGoToAClosedCollectStep()
    {
        $this->visitPageWithParams('collect page', self::$collectStepClosedParams);
        $this->getSession()->wait(3000, "$('#proposal__step-page-rendered').length > 0");
    }

    /**
     * @When I go to a proposal
     */
    public function iGoToAProposal()
    {
        $this->visitPageWithParams('proposal page', self::$proposalWithSimpleVoteParams);
        $this->getSession()->wait(5000, "document.body.innerHTML.toString().indexOf('On va en faire un beau gymnase, promis :)') > -1");
    }

    /**
     * @When I go to a proposal made by msantostefano@jolicode.com
     */
    public function iGoToAProposalMadeByMSantoStefano()
    {
        $this->visitPageWithParams('proposal page', self::$proposalByMSantoStefano);
        $this->getSession()->wait(5000, "document.body.innerHTML.toString().indexOf('On va en faire un beau gymnase, promis :)') > -1");
    }

    /**
     * @When I go to a proposal followed by user
     */
    public function iGoToAProposalFollowedByUser()
    {
        $this->visitPageWithParams('proposal page', self::$proposalWithOneFollower);
        $this->getSession()->wait(5000, "document.body.innerHTML.toString().indexOf('On va en faire un beau gymnase, promis :)') > -1");
    }

    /**
     * @When I go to a proposal made by user@test.com
     */
    public function iGoToAProposalMadeByUser()
    {
        $this->visitPageWithParams('proposal page', self::$proposalsByUserTest);
        $this->getSession()->wait(5000, "document.body.innerHTML.toString().indexOf('On va en faire un beau gymnase, promis :)') > -1");
    }

    /**
     * @When I go to a proposal which is comment notifiable
     */
    public function iGoToACommentNotifiableProposal()
    {
        $this->visitPageWithParams('proposal page', self::$proposalCommentNotifiable);
        $this->getSession()->wait(5000, "document.body.innerHTML.toString().indexOf('On va en faire un beau gymnase, promis :)') > -1");
    }

    /**
     * @When I go to a proposal which is not comment notifiable
     */
    public function iGoToAProposalNotCommentNotifiable()
    {
        $this->visitPageWithParams('proposal page', self::$proposalCommentNotNotifiable);
    }

    /**
     * @When I go to a proposal which is notifiable
     */
    public function iGoToANotifiableProposal()
    {
        $this->visitPageWithParams('proposal page', self::$proposalNotifiable);
    }

    /**
     * @When I go to a proposal of mine which is notifiable
     */
    public function iGoToANotifiableProposalOfMine()
    {
        $this->visitPageWithParams('proposal page', self::$proposalNotifiableOfMine);
    }

    /**
     * @When I go to a proposal which is not notifiable
     */
    public function iGoToANotNotifiableProposal()
    {
        $this->visitPageWithParams('proposal page', self::$proposalNotNotifiable);
    }

    /**
     * @When I go to a proposal not yet votable
     */
    public function iGoToAProposalNotYetVotable()
    {
        $this->visitPageWithParams('proposal page', self::$proposalNotYetVotable);
    }

    /**
     * @When I go to a proposal not votable anymore
     */
    public function iGoToAProposalNotVotableAnymore()
    {
        $this->visitPageWithParams('proposal page', self::$proposalNotVotableAnymore);
    }

    /**
     * @Then there should be :nb proposals
     *
     * @param int $nb
     */
    public function thereShouldBeNbProposals(int $nb)
    {
        $this->assertPageMatchesText('/(proposal.count_with_total {"num":' . $nb . '|proposal.count {"num":' . $nb . '})/');
        $proposalSelector = $this->getCurrentPage()->getProposalSelector();
        $this->assertNumElements($nb, $proposalSelector);
    }

    /**
     * @When I change the proposals theme filter
     */
    public function iChangeTheProposalsThemeFilter()
    {
        $this->selectOption('proposal-filter-themes', 'Justice');
        $this->iWait(2);
    }

    /**
     * @When /^I change the proposals status filter to "([^"]*)"$/
     */
    public function iChangeTheProposalStatusTo(string $status)
    {
        $this->selectOption('proposal-filter-statuses', $status);
        $this->iWait(2);
    }

    /**
     * @When /^I change the proposals contributor type filter to "([^"]*)"$/
     */
    public function iChangeTheProposalContributorTypeTo(string $type)
    {
        $this->selectOption('proposal-filter-types', $type);
        $this->iWait(2);
    }

    /**
     * @When I sort proposals by date
     */
    public function iSortProposalsByDate()
    {
        $this->getCurrentPage()->sortByDate();
        $this->iWait(2);
    }

    /**
     * @When I sort proposals by comments
     */
    public function iSortProposalsByComments()
    {
        $this->getCurrentPage()->sortByComments();
        $this->iWait(1);
    }

    /**
     * @When I search for proposals with terms :terms
     *
     * @param mixed $terms
     */
    public function iSearchForProposalsWithTerms($terms)
    {
        $this->fillField('proposal-search-input', $terms);
        $this->pressButton('proposal-search-button');
        $this->iWait(1);
    }

    /**
     * @Then proposals should be ordered by date
     */
    public function proposalsShouldBeOrderedByDate()
    {
        $option = $this->getCurrentPage()->getSelectedSortingOption();
        \PHPUnit_Framework_Assert::assertEquals('last', $option);
        $this->proposalBeforeProposal(
            'Rénovation du gymnase',
            'Ravalement de la façade de la bibliothèque municipale'
        );
    }

    /**
     * @Then proposals should be ordered randomly
     */
    public function proposalsShouldBeOrderedRandomly()
    {
        $option = $this->getCurrentPage()->getSelectedSortingOption();
        \PHPUnit_Framework_Assert::assertEquals('random', $option);
    }

    /**
     * @Then proposals should be ordered by comments
     */
    public function proposalsShouldBeOrderedByComments()
    {
        $option = $this->getCurrentPage()->getSelectedSortingOption();
        \PHPUnit_Framework_Assert::assertEquals('comments', $option);
        $this->proposalBeforeProposal(
            'Ravalement de la façade de la bibliothèque municipale',
            'Rénovation du gymnase'
        );
    }

    /**
     * @Then proposals should be filtered by terms
     */
    public function proposalsShouldBeFilteredByTerms()
    {
        $this->assertPageContainsText('Proposition pas encore votable');
        $this->assertPageContainsText('Proposition plus votable');
    }

    /**
     * @Then proposals should be filtered by references
     */
    public function proposalsShouldBeFilteredByReferences()
    {
        $this->assertPageContainsText('Proposition pas encore votable');
    }

    /**
     * @Then proposals should be filtered by theme and terms and sorted by comments
     */
    public function proposalsShouldBeFilteredByThemeAndTermsAndSortedByComments()
    {
        $option = $this->getCurrentPage()->getSelectedSortingOption();
        \PHPUnit_Framework_Assert::assertEquals('comments', $option);
        $this->assertPageContainsText('Proposition pas encore votable');
        $this->assertPageContainsText('Proposition plus votable');
        $this->proposalBeforeProposal(
            'Proposition pas encore votable',
            'Proposition plus votable'
        );
    }

    /**
     * @Then proposals should have no results
     */
    public function proposalsShouldHaveNoResults()
    {
        $this->assertPageContainsText('proposal.empty');
        $this->assertPageNotContainsText('proposal.random_search');
    }

    /**
     * @Then I click the create proposal button
     */
    public function iClickTheCreateProposalButton()
    {
        $this->navigationContext->getPage('collect page')->clickCreateProposalButton();
        $this->iWait(1);
    }

    /**
     * @Then I should see the proposal private field
     */
    public function iShouldSeeTheProposalPrivateField()
    {
        $this->assertPageContainsText('global.form.private');
        $this->assertPageContainsText("Evaluez l'importance de votre proposition");
        $this->assertPageContainsText('souhaitable');
    }

    /**
     * @Then I should not see the proposal private field
     */
    public function iShouldNotSeeTheProposalPrivateField()
    {
        $this->assertPageNotContainsText('global.form.private');
        $this->assertPageNotContainsText("Evaluez l'importance de votre proposition");
        $this->assertPageNotContainsText('souhaitable');
    }

    /**
     * @When I fill the proposal form
     */
    public function iFillTheProposalForm()
    {
        $this->fillProposalForm();
    }

    /**
     * @When I comment :body
     *
     * @param string $body
     */
    public function iComment(string $body)
    {
        $this->fillComment($body);
        $this->iSubmitTheCommentForm();
    }

    /**
     * @When I anonymously comment :body as :name with address :email
     *
     * @param string $body
     * @param string $name
     * @param string $email
     */
    public function iAnonymouslyComment(string $body, string $name, string $email)
    {
        $this->fillAnonymousComment($body, $name, $email);
        $this->iSubmitTheCommentForm();
    }

    /**
     * @When I fill the proposal form with a theme
     */
    public function iFillTheProposalFormWithATheme()
    {
        $this->fillProposalForm(true, true);
    }

    /**
     * @When I fill the proposal form without required response
     */
    public function iFillTheProposalFormWithoutRequiredResponse()
    {
        $this->fillProposalForm(true, false, false);
    }

    /**
     * @When I submit the create proposal form
     */
    public function iSubmitTheCreateProposalForm()
    {
        $this->navigationContext->getPage('collect page')->submitProposalForm();
        // We wait for page reloading and new proposal show up
        $this->getSession()->wait(8000, "document.getElementById('proposal.infos.header')");
    }

    /**
     * @When I submit the comment form
     */
    public function iSubmitTheCommentForm()
    {
        $this->navigationContext->getPage('proposal page')->submitCommentForm();
        $this->iWait(5);
    }

    /**
     * @When I submit the edit proposal form
     */
    public function iSubmitTheEditProposalForm()
    {
        $this->navigationContext->getPage('proposal page')->submitEditProposalForm();
        $this->iWait(2);
    }

    /**
     * @Then the create proposal button should be disabled
     */
    public function theCreateProposalButtonShouldBeDisabled()
    {
        $button = $this->navigationContext->getPage('collect page')->getCreateProposalButton();
        \PHPUnit_Framework_Assert::assertTrue($button->hasAttribute('disabled'));
    }

    /**
     * @Then I should see my new proposal
     */
    public function iShouldSeMyNewProposal()
    {
        $this->assertPageContainsText('Nouvelle proposition créée');
    }

    /**
     * @Then I click the edit proposal button
     */
    public function iClickTheEditProposalButton()
    {
        $this->navigationContext->getPage('proposal page')->clickEditProposalButton();
        $this->iWait(1);
    }

    /**
     * @When I click the edit comment button
     */
    public function iClickTheEditCommentButton()
    {
        $this->navigationContext->getPage('proposal page')->clickEditCommentButton();
    }

    /**
     * @When I fill and submit the edit comment form with :body
     *
     * @param string $body
     */
    public function iFillTheEditCommentForm(string $body)
    {
        $this->navigationContext->getPage('edit comment page')->fillEditForm($body);
        $this->iWait(1);
    }

    /**
     * @When I change the proposal title
     */
    public function iChangeTheProposalTitle()
    {
        $this->fillField('proposal_title', 'Nouveau titre');
    }

    /**
     * @Then the proposal title should have changed
     */
    public function theProposalTitleShouldHaveChanged()
    {
        $this->assertPageNotContainsText('Rénovation du gymnase');
        $this->iWait(1);
        $this->assertPageContainsText('Nouveau titre');
    }

    /**
     * @Then I should not see the edit proposal button
     */
    public function iShouldNotSeeTheEditProposalButton()
    {
        $selector = $this->navigationContext->getPage('proposal page')->getUpdateButtonSelector();
        $this->assertElementNotOnPage($selector);
    }

    /**
     * @Then I click the delete proposal button
     */
    public function iClickTheDeleteProposalButton()
    {
        $this->navigationContext->getPage('proposal page')->clickDeleteProposalButton();
        $this->iWait(1);
    }

    /**
     * @Then I confirm proposal deletion
     */
    public function iConfirmProposalDeletion()
    {
        $this->navigationContext->getPage('proposal page')->clickConfirmDeleteProposalButton();
        $this->iWait(3);
        $this->currentPage = 'collect page';
    }

    /**
     * @Then I should not see my proposal anymore
     */
    public function iShouldNotSeeMyProposalAnymore()
    {
        $this->assertPageNotContainsText('Rénovation du gymnase');
    }

    /**
     * @Then I should not see the delete proposal button
     */
    public function iShouldNotSeeTheDeleteProposalButton()
    {
        $selector = $this->navigationContext->getPage('proposal page')->getDeleteButtonSelector();
        $this->assertElementNotOnPage($selector);
    }

    /**
     * @Then I click the report proposal button
     */
    public function iClickTheReportProposalButton()
    {
        $this->navigationContext->getPage('proposal page')->clickReportProposalButton();
        $this->iWait(1);
    }

    /**
     * @Then I should see the proposal likers
     */
    public function iShouldSeeTheProposalLikers()
    {
        $this->assertPageContainsText('proposal.likers.count {"num":5}');
    }

    /**
     * @When I click the share proposal button
     */
    public function iClickTheShareProposalButton()
    {
        $this->navigationContext->getPage('proposal page')->clickShareButton();
        $this->iWait(1);
    }

    // ********************************** Proposal votes *************************************************

    /**
     * @When I go to the votes details page
     */
    public function iGoToTheVotesDetailsPage()
    {
        $this->visitPageWithParams('project user votes page', self::$votesDetailsPageParams);
    }

    /**
     * @When I go to a selection step with simple vote enabled
     */
    public function iGoToASelectionStepWithSimpleVoteEnabled()
    {
        $this->visitPageWithParams('selection page', self::$selectionStepWithSimpleVoteParams);
    }

    /**
     * @When I go to a selection step with budget vote enabled
     */
    public function iGoToASelectionStepWithBudgetVoteEnabled()
    {
        $this->visitPageWithParams('selection page', self::$selectionStepWithBudgetVoteParams);
    }

    /**
     * @When I go to a selection step with budget vote limited enabled
     */
    public function iGoToASelectionStepWithBudgetVoteLimitedEnabled()
    {
        $this->visitPageWithParams('selection page', self::$selectionStepWithBudgetVoteLimitedParams);
    }

    /**
     * @When I go to a selection step not yet open
     */
    public function iGoToASelectionStepNotYetOpen()
    {
        $this->visitPageWithParams('selection page', self::$selectionStepNotYetOpen);
    }

    /**
     * @When I go to a closed selection step
     */
    public function iGoToAClosedSelectionStep()
    {
        $this->visitPageWithParams('selection page', self::$selectionStepClosed);
    }

    /**
     * @When I go to a proposal with budget vote enabled
     */
    public function iGoToAProposalWithBudgetVoteEnabled()
    {
        $this->visitPageWithParams('proposal page', self::$proposalWithBudgetVoteParams);
    }

    /**
     * @Then I should have :nb votes
     */
    public function iShouldHaveNbVotes(int $nb)
    {
        $count = $this->navigationContext->getPage('project user votes page')->countVotes();
        expect($count)->toBe($nb);
    }

    /**
     * @Given the proposal has :nb votes
     * @Then the proposal should have :nb votes
     */
    public function theProposalShouldHaveNbVotes(int $nb)
    {
        $this->iWait(2);
        $votesCount = $this->getCurrentPage()->getVotesCount($this->getProposalId());
        \PHPUnit_Framework_Assert::assertEquals($nb, $votesCount, 'Incorrect votes number ' . $votesCount . ' for proposal.');
    }

    /**
     * @Given the proposal has :nb comments
     * @Then the proposal should have :nb comments
     */
    public function theProposalShouldHaveNbComments(int $nb)
    {
        $commentsCount = $this->getCurrentPage()->getCommentsCount($this->getProposalId());
        \PHPUnit_Framework_Assert::assertEquals($nb, $commentsCount, 'Incorrect comments number ' . $commentsCount . ' for proposal.');
    }

    /**
     * @When I remove the first vote
     */
    public function iRemoveTheFirstVote()
    {
        $this->navigationContext->getPage('project user votes page')->removeFirstVote();
        $this->iWait(5);
    }

    /**
     * @When I click the proposal vote button
     * @When I click the proposal :id vote button
     */
    public function iClickTheProposalVoteButton(string $id = null)
    {
        $this->clickProposalVoteButtonWithLabel('proposal.vote.add', $id);
    }

    /**
     * @When I click the proposal unvote button
     */
    public function iClickTheProposalUnvoteButton()
    {
        $this->clickProposalVoteButtonWithLabel('proposal.vote.hasVoted');
    }

    /**
     * @When I click the reload random button
     */
    public function iClickTheReloadRandomButton()
    {
        $this->clickProposalVoteButtonWithLabel('proposal.random_search');
    }

    /**
     * @When I fill the proposal vote form
     */
    public function iFillTheProposalVoteForm()
    {
        $tableNode = new TableNode([
            ['proposal-vote__username', 'test'],
            ['proposal-vote__email', 'test@coucou.fr'],
        ]);
        $this->fillFields($tableNode);
    }

    /**
     * @When I fill the proposal vote form with already used email
     */
    public function iFillTheProposalVoteFormWithAlreadyUsedEmail()
    {
        $tableNode = new TableNode([
            ['proposal-vote__username', 'test'],
            ['proposal-vote__email', 'cheater@test.com'],
        ]);
        $this->fillFields($tableNode);
    }

    /**
     * @When I fill the proposal vote form with a registered email
     */
    public function iFillTheProposalVoteFormWithARegisteredEmail()
    {
        $tableNode = new TableNode([
            ['proposal-vote__username', 'test'],
            ['proposal-vote__email', 'user@test.com'],
        ]);
        $this->fillFields($tableNode);
    }

    /**
     * @Then selection :selectionStepId :proposalId should have status :statusId
     */
    public function proposalSelectionShouldHaveStatus(string $selectionStepId, string $proposalId, string $statusId)
    {
        $selection = $this->getRepository('CapcoAppBundle:Selection')->findOneBy([
          'selectionStep' => $selectionStepId,
          'proposal' => $proposalId,
        ]);
        expect($selection->getStatus()->getId())->toBe($statusId);
        $this->getEntityManager()->clear();
    }

    /**
     * @Then selection :selectionStepId :proposalId should have no status
     */
    public function proposalSelectionShouldHaveNoStatus(string $selectionStepId, string $proposalId)
    {
        $selection = $this->getRepository('CapcoAppBundle:Selection')->findOneBy([
          'selectionStep' => $selectionStepId,
          'proposal' => $proposalId,
        ]);
        expect($selection->getStatus())->toBe(null);
        $this->getEntityManager()->clear();
    }

    /**
     * @Then proposal :proposalId should have status :statusId
     */
    public function proposalShouldHaveStatus(string $proposalId, string $statusId)
    {
        $proposal = $this->getRepository('CapcoAppBundle:Proposal')->find($proposalId);
        expect($proposal->getStatus()->getId())->toBe($statusId);
        $this->getEntityManager()->clear();
    }

    /**
     * @Then proposal :proposalId should not have a status
     */
    public function proposalShouldHaveNoStatus(string $proposalId)
    {
        $proposal = $this->getRepository('CapcoAppBundle:Proposal')->find($proposalId);
        expect($proposal->getStatus())->toBe(null);
        $this->getEntityManager()->clear();
    }

    /**
     * @Then proposal :proposalId should be selected in selection step :stepId
     */
    public function proposalShouldBeSelected(string $proposalId, string $selectionStepId)
    {
        $this->getEntityManager()->clear();
        $selection = $this->getRepository('CapcoAppBundle:Selection')->findOneBy([
          'selectionStep' => $selectionStepId,
          'proposal' => $proposalId,
        ]);
        expect($selection)->toNotBe(null);
    }

    /**
     * @Then proposal :proposalId should not be selected in selection step :stepId
     */
    public function proposalShouldNotBeSelected(string $proposalId, string $selectionStepId)
    {
        $this->getEntityManager()->clear();
        $selection = $this->getRepository('CapcoAppBundle:Selection')->findOneBy([
          'selectionStep' => $selectionStepId,
          'proposal' => $proposalId,
        ]);
        expect($selection)->toBe(null);
    }

    /**
     * @When I add a proposal vote comment
     */
    public function iAddAProposalVoteComment()
    {
        $this->fillField('proposal-vote__comment', 'Coucou !');
    }

    /**
     * @When I check the proposal vote private checkbox
     */
    public function iCheckTheProposalVotePrivateCheckbox()
    {
        $this->getSession()->getPage()->find('css', '#proposal2-proposal-vote__private .form-group .react-toggle')->click();
    }

    /**
     * @When I submit the proposal vote form
     */
    public function iSubmitTheProposalVoteForm()
    {
        $page = $this->getCurrentPage()->submitProposalVoteForm();
        $this->iWait(8); // We wait for alert to disappear
    }

    /**
     * @Given :userSlug has voted for proposal :proposalId in selection step :stepSlug
     */
    public function userHasVotedForProposalForStep(string $userSlug, string $proposalId, string $stepSlug)
    {
        $user = $this->getRepository('CapcoUserBundle:User')->findOneBySlug($userSlug);
        $proposal = $this->getRepository('CapcoAppBundle:Proposal')->find($proposalId);
        $step = $this->getRepository('CapcoAppBundle:Steps\SelectionStep')->findOneBySlug($stepSlug);
        \PHPUnit_Framework_Assert::assertNotNull(
          $this->getRepository('CapcoAppBundle:ProposalSelectionVote')
               ->findOneBy(['user' => $user, 'proposal' => $proposal, 'selectionStep' => $step])
        );
    }

    /**
     * @Then the proposal vote button must be disabled
     * @Then the proposal :id vote button must be disabled
     */
    public function theProposalVoteButtonMustBeDisabled(string $id = null)
    {
        $id = $id ?: $this->getProposalId();
        $button = $this->getCurrentPage()->getVoteButton($id);
        \PHPUnit_Framework_Assert::assertTrue(
            $button->hasClass('disabled') || $button->hasAttribute('disabled'),
            'The proposal vote button is not disabled neither it has class "disabled".'
        );
    }

    /**
     * @Then the proposal vote button with id :id must not be present
     * @Then the proposal vote button must not be present
     */
    public function theProposalVoteButtonWithIdMustNotBePresent(string $id = null)
    {
        $execpetionMessage = $id
            ? '"proposal vote button ' . $id . '" element is not present on the page'
            : '"proposal vote button" element is not present on the page';

        try {
            $button = $this->getCurrentPage()->getVoteButton($this->getProposalId());
        } catch (\Exception $e) {
            \PHPUnit_Framework_Assert::assertSame($execpetionMessage, $e->getMessage());
        }
    }

    /**
     * @When I should see the proposal vote limited tooltip
     */
    public function iShouldSeeTheProposalVoteLimitedTooltip()
    {
        $this->assertElementContainsText('#vote-tooltip-proposal-proposal18', 'proposal.vote.popover.limit_reached_title');
    }

    /**
     * @When I should see the proposal vote tooltip
     */
    public function iShouldSeeTheProposalVoteTooltip()
    {
        $this->assertElementContainsText('#vote-tooltip-proposal-proposal8', 'proposal.vote.popover.not_enough_credits_text');
    }

    /**
     * @Then I should see my comment in the proposal comments list
     */
    public function iShouldSeeMyCommentInTheProposalCommentsList()
    {
        $this->assertProposalCommentsContains('Coucou !');
    }

    /**
     * @Then I should see my vote in the proposal votes list
     */
    public function iShouldSeeMyVoteInTheProposalVotesList()
    {
        $this->assertFirstProposalVoteContains('user');
    }

    /**
     * @Then I should not see my vote in the proposal votes list
     */
    public function iShouldNotSeeMyVoteInTheProposalVotesList()
    {
        $this->assertFirstProposalVoteNotContains('user');
    }

    /**
     * @Then I should see my anonymous vote in the proposal votes list
     */
    public function iShouldSeeMyAnonymousVoteInTheProposalVotesList()
    {
        $this->assertFirstProposalVoteContains('global.anonymous');
    }

    /**
     * @Then I should see my not logged in vote in the proposal votes list
     */
    public function iShouldSeeMyNotLoggedInVoteInTheProposalVotesList()
    {
        $this->assertFirstProposalVoteContains('test');
    }

    /**
     * @Then I should see my subscription as :username in the proposal followers list
     *
     * @param mixed $username
     */
    public function iShouldSeeMySubscriptionInTheProposalFollowersList($username)
    {
        $this->assertFirstProposalFollowerContains($username);
    }

    /**
     * @Then I should not see my subscription as :username in the proposal followers list
     *
     * @param mixed $username
     */
    public function iShouldNotSeeMySubscriptionInTheProposalFollowersList($username)
    {
        $this->assertFirstProposalFollowerNotContains($username);
    }

    /**
     * @Then I should not see my subscription on the proposal followers list
     */
    public function iShouldNotSeeMySubscriptionOnTheProposalFollowersList()
    {
        $this->assertFirstProposalFollowerNotOnPage();
    }

    /**
     * @When I go to the proposal votes tab
     */
    public function iGoToTheProposalVotesTab()
    {
        $page = $this->getCurrentPage();
        $this->iWait(3); // Wait alert to disappear
        $this->getSession()->wait(3000, "$('" . $page->getSelector('votes tab') . "').length > 0");
        $page->clickVotesTab();
        $this->iWait(1);
    }

    /**
     * @When I go to the proposal followers tab
     */
    public function iGoToTheProposalFollowersTab()
    {
        $page = $this->getCurrentPage();
        $this->iWait(3); // Wait alert to disappear
        $this->getSession()->wait(3000, "$('" . $page->getSelector('followers tab') . "').length > 0");
        $page->clickFollowersTab();
        $this->iWait(1);
    }

    /**
     * @When I click the proposal follow button on :proposalId
     */
    public function iClickTheProposalFollowButton(string $proposalId)
    {
        $page = $this->getCurrentPage();
        $page->clickFollowButton($proposalId);
        $this->iWait(2);
    }

    /**
     * @When I click on :choice choice on :proposalId
     */
    public function iClickOnFollow(string $choice, string $proposalId)
    {
        $page = $this->getCurrentPage();
        $page->clickFollowChoice($choice, $proposalId);
    }

    /**
     * @When I should see advancement checked on :proposalId
     */
    public function iShouldSeeAdvancementCheckedOnProposal(string $proposalid)
    {
        $page = $this->getCurrentPage();
        $page->followAdvancementIsChecked($proposalid);
    }

    /**
     * @When I should see follow advancement and comment checked on :proposalId
     */
    public function iShouldSeeFollowAdvancementAndCommentCheckedOnProposal(string $proposalid)
    {
        $page = $this->getCurrentPage();
        $page->followAdvancementAndCommentIsChecked($proposalid);
    }

    /**
     * @When I should see follow all activities checked on :proposalId
     */
    public function iShouldSeeFollowAllActivitiesCheckedOnProposal(string $proposalid)
    {
        $page = $this->getCurrentPage();
        $page->followAllIsChecked($proposalid);
    }

    /**
     * @When I click the proposal unfollow button on :proposalId
     */
    public function iClickTheProposalUnfollowButton(string $proposalId)
    {
        $page = $this->getCurrentPage();
        $page->clickUnfollowButton($proposalId);
        $this->iWait(2);
    }

    /**
     * @When I go to the proposal comments tab
     */
    public function iGoToTheProposalCommentsTab()
    {
        $page = $this->getCurrentPage();
        $this->iWait(3); // Wait alert to disappear
        $this->getSession()->wait(3000, "$('" . $page->getSelector('comments tab') . "').length > 0");
        $page->clickCommentsTab();
        $this->iWait(1);
    }

    /**
     * @Then I should have :filesNumber files in media folder
     */
    public function iShouldHaveXFilesInMediaFolder(int $filesNumber)
    {
        $filesCount = iterator_count(
            new FilesystemIterator('/var/www/web/media/default/0001/01', FilesystemIterator::SKIP_DOTS)
        );
        \PHPUnit_Framework_Assert::assertSame($filesNumber, $filesCount);
    }

    /**
     * @When I save current proposals
     */
    public function iSaveCurrentProposals()
    {
        $items = array_map(
            function ($element) {
                return $element->getText();
            },
            $this->getSession()->getPage()->findAll('css', '.opinion__list .card__title')
        );

        $this->currentCollectsStep = $items;
    }

    /**
     * @When I should see same proposals
     */
    public function iShouldSeeSameProposals()
    {
        $savedSteps = $this->currentCollectsStep;
        $selector = '.opinion__list .card__title';

        $items = array_map(
            function ($element) {
                return $element->getText();
            },
            $this->getSession()->getPage()->findAll('css', $selector)
        );

        \PHPUnit_Framework_Assert::assertSame($savedSteps, $items);
    }

    /**
     * @When I should see other proposals
     */
    public function iShouldSeeOtherProposals()
    {
        $savedSteps = $this->currentCollectsStep;
        $selector = '.opinion__list .card__title span span';

        $items = array_map(
            function ($element) {
                return $element->getText();
            },
            $this->getSession()->getPage()->findAll('css', $selector)
        );

        \PHPUnit_Framework_Assert::assertNotSame($savedSteps, $items);
    }

    /**
     * @When I should not see random row
     */
    public function isShouldNotSeeRandomRow()
    {
        $this->assertPageNotContainsText('proposal.random_search');
    }

    /**
     * @Then I should have :filesNumber files in source media folder
     */
    public function iShouldHaveXFilesInSourceMediaFolder(int $filesNumber)
    {
        if (!is_dir('/var/www/web/media/sources/0001/01')) {
            \PHPUnit_Framework_Assert::assertSame($filesNumber, 0);

            return;
        }

        $filesCount = iterator_count(
            new FilesystemIterator('/var/www/web/media/sources/0001/01', FilesystemIterator::SKIP_DOTS)
        );
        \PHPUnit_Framework_Assert::assertSame($filesNumber, $filesCount);
    }

    protected function openCollectStepIsOpen()
    {
        return $this->navigationContext
            ->getPage('collect page')
            ->isOpen(self::$collectStepOpenParams)
            ;
    }

    protected function closedCollectStepIsOpen()
    {
        return $this->navigationContext
            ->getPage('collect page')
            ->isOpen(self::$collectStepClosedParams)
            ;
    }

    protected function proposalPageIsOpen()
    {
        return $this->navigationContext->getPage('proposal page')->isOpen(self::$proposalWithSimpleVoteParams);
    }

    protected function proposalNotYetVotablePageIsOpen()
    {
        return $this->navigationContext->getPage('proposal page')->isOpen(self::$proposalNotYetVotable);
    }

    protected function proposalNotVotableAnymoreIsOpen()
    {
        return $this->navigationContext->getPage('proposal page')->isOpen(self::$proposalNotVotableAnymore);
    }

    protected function proposalBeforeProposal($proposal1, $proposal2)
    {
        $this->element1ShouldBeBeforeElement2ForSelector(
            $proposal1,
            $proposal2,
            '.proposal-preview .card__title'
        );
    }

    protected function fillProposalForm($fillDistrict = false, $fillTheme = false, $requiredResponse = 'Réponse à la question 2')
    {
        $tableNode = new TableNode([
            ['proposal_title', 'Nouvelle proposition créée'],
            ['proposal_body', 'Description de ma proposition'],
            ['responses[0]', 'Réponse à la question 1'],
            ['proposal_address', '5 Allée Rallier-du-Baty 35000 Rennes'],
        ]);
        if (false !== $requiredResponse) {
            $this->fillField('responses[1]', $requiredResponse);
        }
        $this->fillFields($tableNode);
        $this->selectOption('proposal_category', 'Politique');
        if ($fillTheme) {
            $this->selectOption('proposal_theme', 'Justice');
        }
        if ($fillDistrict) {
            $this->selectOption('proposal_district', 'Beauregard');
        }
        $this->iWait(1);
        $this->iClickElement('#PlacesAutocomplete__autocomplete-container > div:first-child');
        $this->iWait(1);
    }

    protected function fillComment($body)
    {
        $this->fillField('body', $body);
    }

    protected function fillAnonymousComment($body, $name, $email)
    {
        $this->fillField('body', $body);
        $this->fillField('authorName', $name);
        $this->fillField('authorEmail', $email);
    }

    protected function votesDetailsPageIsOpen()
    {
        return $this->navigationContext->getPage('project user votes page')->isOpen();
    }

    protected function selectionStepWithSimpleVoteIsOpen()
    {
        return $this->navigationContext
            ->getPage('selection page')
            ->isOpen(self::$selectionStepWithSimpleVoteParams)
            ;
    }

    protected function selectionStepWithBudgetVoteIsOpen()
    {
        return $this->navigationContext
            ->getPage('selection page')
            ->isOpen(self::$selectionStepWithBudgetVoteParams)
            ;
    }

    protected function selectionStepNotYetOpenIsOpen()
    {
        return $this->navigationContext
            ->getPage('selection page')
            ->isOpen(self::$selectionStepNotYetOpen)
            ;
    }

    protected function selectionStepClosedIsOpen()
    {
        return $this->navigationContext
            ->getPage('selection page')
            ->isOpen(self::$selectionStepClosed)
            ;
    }

    protected function proposalPageWithBudgetVoteIsOpen()
    {
        return $this->navigationContext->getPage('proposal page')->isOpen(self::$proposalWithBudgetVoteParams);
    }

    protected function getProposalId(): string
    {
        if ($this->proposalPageIsOpen() || $this->selectionStepWithSimpleVoteIsOpen()) {
            return 'proposal2';
        }
        if ($this->proposalPageWithBudgetVoteIsOpen() || $this->selectionStepWithBudgetVoteIsOpen()) {
            return 'proposal8';
        }
        if ($this->selectionStepNotYetOpenIsOpen() || $this->proposalNotYetVotablePageIsOpen()) {
            return 'proposal10';
        }
        if ($this->selectionStepClosedIsOpen() || $this->proposalNotVotableAnymoreIsOpen()) {
            return 'proposal11';
        }

        throw new \Exception('Unknown proposalId');
    }

    protected function clickProposalVoteButtonWithLabel(string $label, string $id = null)
    {
        $page = $this->getCurrentPage();
        $proposalId = $id ?: $this->getProposalId();
        $buttonLabel = $page->getVoteButtonLabel($proposalId);
        \PHPUnit_Framework_Assert::assertEquals($label, $buttonLabel, 'Incorrect button label ' . $buttonLabel . ' on proposal vote button.');
        $page->clickVoteButton($proposalId);
        $this->iWait(2);
    }

    protected function assertProposalCommentsContains($text)
    {
        $firstVoteSelector = $this->navigationContext->getPage('proposal page')->getCommentsListSelector();
        $this->assertElementContainsText($firstVoteSelector, $text);
    }

    protected function assertFirstProposalVoteContains($text)
    {
        $firstVoteSelector = $this->navigationContext->getPage('proposal page')->getFirstVoteSelector();
        $this->assertElementContainsText($firstVoteSelector, $text);
    }

    // TODO : refactor all assert proposal in one more scalable function ?
    protected function assertFirstProposalFollowerContains($text)
    {
        $lastFollowerSelector = $this->navigationContext->getPage('proposal page')->getLastSelector('follower');
        $this->assertElementContainsText($lastFollowerSelector, $text);
    }

    protected function assertFirstProposalVoteNotContains($text)
    {
        $firstVoteSelector = $this->navigationContext->getPage('proposal page')->getFirstVoteSelector();
        $this->assertElementNotContainsText($firstVoteSelector, $text);
    }

    protected function assertFirstProposalFollowerNotContains(string $text)
    {
        $lastFollowerSelector = $this->navigationContext->getPage('proposal page')->getLastSelector('follower');
        if ('null' === $text) {
            $this->assertElementNotOnPage($lastFollowerSelector);
        } else {
            $this->assertElementNotContainsText($lastFollowerSelector, $text);
        }
    }

    protected function assertFirstProposalFollowerNotOnPage()
    {
        $lastFollowerSelector = $this->navigationContext->getPage('proposal page')->getLastSelector('follower');
        $this->assertElementNotOnPage($lastFollowerSelector);
    }
}
