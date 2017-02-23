<?php
/**
 * Copyright (c) 2010-2017, AWHSPanel by Nicolas Méloni
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 *  Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 *  Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 *  Neither the name of AWHSPanel nor the names of its contributors
 *   may be used to endorse or promote products derived from this software without
 *   specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 * ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 */

namespace AWHS\UrlShortenerBundle\Controller;

use AWHS\UrlShortenerBundle\Entity\UrlShortener;
use AWHS\UrlShortenerBundle\Entity\UrlShortenerVisit;
use AWHS\UrlShortenerBundle\Lib\Utils;
use Doctrine\ORM\NoResultException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    public function helloAction()
    {
        return $this->render("AWHSUrlShortenerBundle:" . $this->container->getParameter('awhs')['template'] . ":interface.html.twig", array());
    }

    public function indexAction(Request $request, $shortened)
    {
        $em = $this->getDoctrine()->getManager();
        try {
            /**
             * @var \AWHS\UrlShortenerBundle\Entity\UrlShortener $us
             */
            $us = $em->createQuery("
				SELECT us
				FROM AWHSUrlShortenerBundle:UrlShortener us 
				WHERE us.guid='$shortened'")->getSingleResult();

            if ($us->isDeleted()) {
                return $this->render("AWHSUrlShortenerBundle:" . $this->container->getParameter('awhs')['template'] . ":deleted.html.twig", array());
            } else {
                $usv = new UrlShortenerVisit();
                $usv->setDatetime(new \DateTime());
                $usv->setIpAddress($request->getClientIp());
                $usv->setUrlShortened($us);
                $em->persist($usv);
                $em->flush();
                return $this->redirect($us->getUrl(), 302);
            }
        } catch (NoResultException $ex) {
            return new Response(0);
        }

    }

    public function interfaceAction()
    {
        return $this->render("AWHSUrlShortenerBundle:" . $this->container->getParameter('awhs')['template'] . ":interface.html.twig", array());
    }

    public function createAction(Request $request)
    {
        /**
         * @Assert\Url(
         *    protocols = {"http", "https"}
         * )
         */
        $url = $_POST['url'];
        if (empty($url)) {
            return new Response("URL is missing", 400);
        }
        if (!Utils::startsWith($url, "http://") && !Utils::startsWith($url, "https://")) {
            return new Response("URL not valid", 400);
        } else if ($url == "http://" || $url == "https://" || Utils::startsWith($url, "http://your_domain.com") || Utils::startsWith($url, "https://your_domain.com")) {
            return new Response("URL not valid", 400);
        }
        $us = new UrlShortener();
        $guid = Utils::random(4);
        $us->setGuid($guid);
        $us->setUrl($url);
        $us->setDatetime(new \DateTime());
        $us->setIpAddress($request->getClientIp());
        $us->setIsDeleted(false);
        $em = $this->getDoctrine()->getManager();
        $em->persist($us);
        $em->flush();
        return new JsonResponse(array('url' => "https://your_domain.com/{$guid}"));
    }
}
