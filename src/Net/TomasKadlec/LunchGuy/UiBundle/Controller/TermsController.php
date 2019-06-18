<?php

namespace Net\TomasKadlec\LunchGuy\UiBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TermsController extends Controller
{

    /**
     * @Route("/terms", methods={"GET"})
     * @Template()
     */
    public function indexAction()
    {
        return [];
    }

}