<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

namespace App\Controller\Sales;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ParticipantsController extends Controller
{
    /**
     * @Route("/sales/participants", name="sales_participants")
     */
    public function index()
    {
        return $this->render('sales/participants.html.twig', [
            'controller_name' => 'ParticipantsController'
        ]);
    }
}
