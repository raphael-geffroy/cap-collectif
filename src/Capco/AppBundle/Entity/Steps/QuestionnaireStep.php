<?php

namespace Capco\AppBundle\Entity\Steps;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Capco\AppBundle\Entity\Questionnaire;

/**
 * Class QuestionnaireStep.
 *
 * @ORM\Table(name="questionnaire_step")
 * @ORM\Entity(repositoryClass="Capco\AppBundle\Repository\QuestionnaireStepRepository")
 * @Serializer\ExclusionPolicy("all")
 */
class QuestionnaireStep extends AbstractStep
{
    const VERIFICATION_NONE = 'none';
    const VERIFICATION_SMS = 'sms';
    public static $verificationLabels = [
        self::VERIFICATION_NONE => 'step.verification.none',
        self::VERIFICATION_SMS => 'step.verification.sms',
    ];

    /**
     * @ORM\Column(type="string", columnDefinition="ENUM('none', 'sms')")
     */
    private $verification = 'none';

    /**
     * @var int
     *
     * @ORM\Column(name="replies_count", type="integer")
     */
    private $repliesCount = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="contributors_count", type="integer")
     */
    private $contributorsCount = 0;

    /**
     * @var Questionnaire
     * @ORM\OneToOne(targetEntity="Capco\AppBundle\Entity\Questionnaire", mappedBy="step", cascade={"persist", "remove"})
     */
    private $questionnaire = null;

    /**
     * @return string
     */
    public function getType()
    {
        return 'questionnaire';
    }

    /**
     * @return bool
     */
    public function isQuestionnaireStep()
    {
        return true;
    }

    /**
     * @return int
     */
    public function getContributorsCount()
    {
        return $this->contributorsCount;
    }

    /**
     * @param int $contributorsCount
     *
     * @return $this
     */
    public function setContributorsCount($contributorsCount)
    {
        $this->contributorsCount = $contributorsCount;

        return $this;
    }

    /**
     * @return int
     */
    public function getRepliesCount()
    {
        return $this->repliesCount;
    }

    public function setVerification($verification)
    {
        if (!in_array($verification, array(self::VERIFICATION_NONE, self::VERIFICATION_SMS))) {
            throw new \InvalidArgumentException("Invalid verification.");
        }
        $this->verification = $verification;

        return $this;
    }

    public function getVerification()
    {
        return $this->verification;
    }

    /**
     * @param int $repliesCount
     *
     * @return $this
     */
    public function setRepliesCount($repliesCount)
    {
        $this->repliesCount = $repliesCount;

        return $this;
    }

    /**
     * @return Questionnaire
     */
    public function getQuestionnaire()
    {
        return $this->questionnaire;
    }

    public function isSmsConfirmationRequired()
    {
      return $this->verification === self::VERIFICATION_NONE;
    }

    /**
     * @param Questionnaire $questionnaire
     *
     * @return QuestionnaireStep
     */
    public function setQuestionnaire(Questionnaire $questionnaire = null)
    {
        if ($questionnaire) {
            $questionnaire->setStep($this);
        }
        $this->questionnaire = $questionnaire;

        return $this;
    }
}
