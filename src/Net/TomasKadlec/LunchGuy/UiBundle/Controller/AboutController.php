<?php
namespace Net\TomasKadlec\LunchGuy\UiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AboutController extends Controller
{

    /**
     * @Route("/about", methods={"GET"})
     * @Template()
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * @Route("/contribute", methods={"GET"})
     * @Template()
     */
    public function contributeAction()
    {
        return [];
    }

    /**
     * @Route("/contributors", methods={"GET"})
     * @Template()
     */
    public function contributorsAction()
    {
        try {
            $data = $this
                ->get('net_tomas_kadlec_lunch_guy_base.service.contributors')
                ->getContributors();
            return [
                'contributors' => $data['contributors'],
            ];
        } catch (\RuntimeException $e) {
            return [];
        }
    }

}