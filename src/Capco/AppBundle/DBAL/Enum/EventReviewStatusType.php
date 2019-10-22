<?php

namespace Capco\AppBundle\DBAL\Enum;

class EventReviewStatusType extends AbstractEnumType
{
    public const APPROVED = 'approved';
    public const REFUSED = 'refused';
    public const AWAITING = 'awaiting';
    public const NONE = '';
    public const PUBLISHED = 'published';
    public const NOT_PUBLISHED = 'not_published';
    public static $eventReviewStatus = [self::AWAITING, self::REFUSED, self::APPROVED];
    public static $eventStatusesLabels = [
        self::AWAITING => 'waiting',
        self::PUBLISHED => 'project_download.label.published',
        self::NOT_PUBLISHED => 'post_is_not_public',
        self::APPROVED => 'approved',
        self::REFUSED => 'declined'
    ];

    protected $name = 'enum_event_review_status';
    protected $values = [self::AWAITING, self::REFUSED, self::APPROVED, self::NONE];
}
