<?php declare(strict_types=1);
namespace Sas\Esd\Storefront\Controller;

use Sas\Esd\Service\EsdService;
use Shopware\Core\Checkout\Cart\Exception\CustomerNotLoggedInException;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"storefront"})
 */
class DownloadsController extends StorefrontController
{
    /**
     * @var EntityRepositoryInterface
     */
    private $esdOrderRepository;

    /**
     * @var EsdService
     */
    private $esdService;

    public function __construct(
        EntityRepositoryInterface $esdOrderRepository,
        EsdService $esdService
    ) {
        $this->esdOrderRepository = $esdOrderRepository;
        $this->esdService = $esdService;
    }

    /**
     * @Route("/account/downloads", name="frontend.account.downloads.page", options={"seo"="false"}, methods={"GET"})
     *
     * @throws CustomerNotLoggedInException
     */
    public function __invoke(SalesChannelContext $context)
    {
        $this->denyAccessUnlessLoggedIn();

        return $this->renderStorefront(
            'storefront/page/account/downloads/index.html.twig',
            [
                'items' => $this->esdService->getEsdOrderListByCustomer($context),
            ]
        );
    }

    /**
     * @throws CustomerNotLoggedInException
     */
    protected function denyAccessUnlessLoggedIn(bool $allowGuest = false): void
    {
        /** @var RequestStack $requestStack */
        $requestStack = $this->get('request_stack');
        $request = $requestStack->getCurrentRequest();

        if (!$request) {
            throw new CustomerNotLoggedInException();
        }

        /** @var SalesChannelContext|null $context */
        $context = $request->attributes->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_CONTEXT_OBJECT);

        if (
            $context
            && $context->getCustomer()
            && (
                $allowGuest === true
                || $context->getCustomer()->getGuest() === false
            )
        ) {
            return;
        }

        throw new CustomerNotLoggedInException();
    }
}
