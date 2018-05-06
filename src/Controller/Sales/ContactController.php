<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

namespace App\Controller\Sales;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ContactController extends Controller
{
    /**
     * @Route("/sales/contact", name="sales_contact")
     */
    public function index()
    {
        return $this->render('sales/contact.html.twig', [
            'controller_name' => 'ContactController',
        ]);
    }
}
