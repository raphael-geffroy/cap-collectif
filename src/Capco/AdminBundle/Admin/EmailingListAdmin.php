<?php

namespace Capco\AdminBundle\Admin;

use Sonata\AdminBundle\Route\RouteCollectionInterface;

class EmailingListAdmin extends AbstractAdmin
{
    protected $baseRouteName = 'capco_admin_emailing_mailingList';
    protected $baseRoutePattern = 'mailingList';

    public function getFeatures(): array
    {
        return ['beta__emailing'];
    }

    protected function configure(): void
    {
        //$this->setTemplate('list', 'CapcoAdminBundle:Emailing:emailingList.html.twig');
        parent::configure();
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->clearExcept(['list', 'create']);
    }
}
