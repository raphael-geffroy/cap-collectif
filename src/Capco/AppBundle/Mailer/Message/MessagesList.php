<?php

namespace Capco\AppBundle\Mailer\Message;

use Capco\AppBundle\Mailer\Message\CustomDomain\CreateCustomDomainMessage;
use Capco\AppBundle\Mailer\Message\Event\EventCreateAdminMessage;
use Capco\AppBundle\Mailer\Message\Event\EventDeleteAdminMessage;
use Capco\AppBundle\Mailer\Message\Event\EventDeleteMessage;
use Capco\AppBundle\Mailer\Message\Event\EventReviewApprovedMessage;
use Capco\AppBundle\Mailer\Message\Event\EventEditAdminMessage;
use Capco\AppBundle\Mailer\Message\Event\EventReviewRefusedMessage;
use Capco\AppBundle\Mailer\Message\Proposal\ProposalAknowledgeCreateMessage;
use Capco\AppBundle\Mailer\Message\Proposal\ProposalNewsCreateAdminMessage;
use Capco\AppBundle\Mailer\Message\Proposal\ProposalNewsUpdateAdminMessage;
use Capco\AppBundle\Mailer\Message\Proposal\ProposalRevisionMessage;
use Capco\AppBundle\Mailer\Message\Proposal\ProposalRevisionRevisedMessage;
use Capco\AppBundle\Mailer\Message\Proposal\ProposalStatusChangeMessage;
use Capco\AppBundle\Mailer\Message\User\UserAccountConfirmationReminderMessage;
use Capco\AppBundle\Mailer\Message\UserInvite\UserInviteNewInvitation;

final class MessagesList
{
    public const MESSAGES_LIST = [
        'event_create_admin' => EventCreateAdminMessage::class,
        'event_edit_admin' => EventEditAdminMessage::class,
        'event_delete_admin' => EventDeleteAdminMessage::class,
        'event_delete' => EventDeleteMessage::class,
        'event_review_approved' => EventReviewApprovedMessage::class,
        'event_review_refused' => EventReviewRefusedMessage::class,
        'proposal_update_status' => ProposalStatusChangeMessage::class,
        'proposal_revision' => ProposalRevisionMessage::class,
        'proposal_revision_revise' => ProposalRevisionRevisedMessage::class,
        'proposal_news_create' => ProposalNewsCreateAdminMessage::class,
        'proposal_news_update' => ProposalNewsUpdateAdminMessage::class,
        'proposal_acknowledge_create' => ProposalAknowledgeCreateMessage::class,
        'user_account_confirmation_reminder_message' =>
            UserAccountConfirmationReminderMessage::class,
        'user_invitation' => UserInviteNewInvitation::class,
        'custom_domain_create' => CreateCustomDomainMessage::class
    ];
    public const TEMPLATE_LIST = [
        'event_create_admin' => EventCreateAdminMessage::TEMPLATE,
        'event_edit_admin' => EventEditAdminMessage::TEMPLATE,
        'event_delete_admin' => EventDeleteAdminMessage::TEMPLATE,
        'event_delete' => EventDeleteMessage::TEMPLATE,
        'event_review_approved' => '@CapcoMail/Event/notifyUserReviewedEvent.html.twig',
        'event_review_refused' => EventReviewRefusedMessage::TEMPLATE,
        'proposal_update_status' =>
            '@CapcoMail/Proposal/notifyProposalAuthorStatusChange.html.twig',
        'proposal_revision' => '@CapcoMail/notifyProposalRevision.html.twig',
        'proposal_news_create' => ProposalNewsCreateAdminMessage::TEMPLATE,
        'proposal_news_update' => ProposalNewsUpdateAdminMessage::TEMPLATE,
        'proposal_acknowledge_create' => ProposalAknowledgeCreateMessage::TEMPLATE,
        'user_account_confirmation_reminder_message' =>
            UserAccountConfirmationReminderMessage::TEMPLATE,
        'user_invitation' => UserInviteNewInvitation::TEMPLATE,
        'custom_domain_create' => CreateCustomDomainMessage::TEMPLATE
    ];
}
