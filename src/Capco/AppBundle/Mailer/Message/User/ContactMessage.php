<?php

namespace Capco\AppBundle\Mailer\Message\User;

use Capco\AppBundle\Mailer\Message\AdminMessage;

final class ContactMessage extends AdminMessage
{
    public static function create(
        string $recipentEmail,
        string $senderEmail,
        string $senderName,
        string $message,
        string $recipientName = null
    ): self {
        return new self(
            $recipentEmail,
            $recipientName,
            'email-subject-contact',
            static::getMySubjectVars(
                $senderName
            ),
            'email-content-contact',
            static::getMyTemplateVars(
                $message
            ),
            $senderEmail,
            $senderName
        );
    }

    private static function getMyTemplateVars(
        string $message
    ): array {
        return [
            '{message}' => $message,
        ];
    }

    private static function getMySubjectVars(
        string $senderName
    ): array {
        return [
            '{sender}' => $senderName,
        ];
    }
}
