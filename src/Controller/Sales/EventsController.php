<?php
/**
 * Copyright (c) 2018. Mark Garber
 * This work, including the code samples, is licensed under a Creative Commons BY-SA 3.0 license.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 4/13/18
 * Time: 7:27 PM
 */

namespace App\Controller\Sales;


use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class EventsController extends Controller
{
    /**
     * @Route("/sales/events", name="sales_events")
     */
    public function index()
    {
        return $this->render('sales/events.html.twig', [
            'controller_name' => 'EventsController',
        ]);
    }
}
