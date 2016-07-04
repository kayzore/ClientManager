<?php

// src/CM/PlatformBundle/Controller/PlatformController.php

namespace CM\PlatformBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use CM\UserBundle\Entity\User;
use CM\PlatformBundle\Entity\LinkUserClient;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Doctrine\ORM\EntityManager;

class PlatformController extends Controller
{
    public function indexAction()
    {
    	if ($this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
	      return $this->redirectToRoute('cm_platform_list');
	    }
    	return $this->redirectToRoute('fos_user_security_login');
    }
    public function viewAction($id)
    {
    	if (!$this->get('security.context')->isGranted('ROLE_USER')) {
	      throw new AccessDeniedException('Accès limité aux utilisateurs enregistrés.');
	    }

	    $repository = $this->getDoctrine()
	      ->getManager()
	      ->getRepository('CMUserBundle:User')
	    ;

	    $client = $repository->find($id);

	    if (null === $client) {
	      	return $this->render('CMPlatformBundle:Platform:listNoClient.html.twig');
	    }

	    return $this->render('CMPlatformBundle:Platform:view.html.twig', array(
	      'client' => $client, 'id' => $id
	    ));
    }

    public function listAction(Request $request)
    {
	    if (!$this->get('security.context')->isGranted('ROLE_USER')) {
	      throw new AccessDeniedException('Accès limité aux utilisateurs enregistrés.');
	    }

	    $em = $this->getDoctrine()->getManager();

	    $user = $this->container->get('security.context')->getToken()->getUser();
		$user->getId();

		$repository = $this
  			->getDoctrine()
  			->getManager()
  			->getRepository('CMPlatformBundle:LinkUserClient')
		;

		$listMyClients = $repository->findByuserId($user);

		$repository2 = $this
  			->getDoctrine()
  			->getManager()
  			->getRepository('CMUserBundle:User')
		;

		foreach ($listMyClients as $client) {
		  	$listClients[] = $repository2->findByid($client->getUserClientId());
		}

		if (empty($listClients)) {
	      	return $this->render('CMPlatformBundle:Platform:listNoClient.html.twig');
	    }

	    return $this->render('CMPlatformBundle:Platform:list.html.twig', array(
	      'listClients' => $listClients
	    ));
    }

    public function addAction(Request $request)
    {
    	if (!$this->get('security.context')->isGranted('ROLE_USER')) {
	      throw new AccessDeniedException('Accès limité aux utilisateurs enregistrés.');
	    }

    	$client = new User();

	    $form = $this->get('form.factory')->createBuilder('form', $client)
	    	->add('nom',         'text')
	      	->add('prenom',      'text')
	      	->add('email',       'email')
	      	->add('adresse',     'text')
	      	->add('telephone',   'text')
	      	->add('web_site',    'text')
      		->add('sauvegarder', 'submit')
	      	->getForm()
	    ;

	    $form->handleRequest($request);

	    if ($form->isValid()) {
	    	$em = $this->getDoctrine()->getManager();
			$user = $this->container->get('security.context')->getToken()->getUser();
	    	$user->getId();

	    	$userClient = $em->getRepository('CMUserBundle:User')->findOneByEmail($client->getEmail());

	    	if(!is_null($userClient)){
	    		echo "<br />1";
			    var_dump($userClient->getId());

		      	$linkUserClient = new LinkUserClient();
		      	$linkUserClient->setUserId($user->getId());
			    $linkUserClient->setUserClientId($userClient);
				$em->detach($client);
		      	$em->persist($linkUserClient);
		      	$em->flush();

		      	return $this->redirect($this->generateUrl('cm_platform_view', array('id' => $userClient->getId())));
	    	}
	    	else{
	    		echo "<br />2";
		      	$em->persist($client);
		      	$em->flush();

		      	$linkUserClient = new LinkUserClient();
		      	$linkUserClient->setUserId($user->getId());
			    $linkUserClient->setUserClientId($client);
		      	$em->persist($linkUserClient);
		      	$em->flush();
		      	
		      	return $this->redirect($this->generateUrl('cm_platform_view', array('id' => $client->getId())));
	    	}

		    
	    }

	    return $this->render('CMPlatformBundle:Platform:add.html.twig', array(
	      	'form' => $form->createView(),
	    ));
    }

    public function deleteAction(Request $request, $id)
    {
    	if (!$this->get('security.context')->isGranted('ROLE_USER')) {
	      throw new AccessDeniedException('Accès limité aux utilisateurs enregistrés.');
	    }

    	$em = $this->getDoctrine()->getManager();

	    $client = $em->getRepository('CMUserBundle:User')->find($id);

	    if (null === $client) {
	      	return $this->render('CMPlatformBundle:Platform:listNoClient.html.twig');
	    }

	    $form = $this->createFormBuilder()->getForm();

	    if ($form->handleRequest($request)->isValid()) {
	    	$user = $this->container->get('security.context')->getToken()->getUser();
        	$user->getId();
        	if($client->isEnabled() == '1'){
        		$repository = $em->getRepository('CMPlatformBundle:LinkUserClient');
				$clientLink = $repository->findOneBy(array('userId' => $user, 'userClientId' => $id));

				$em->remove($clientLink);

		      	$em->flush();

		      	$request->getSession()->getFlashBag()->add('info', "Le client a bien été supprimée.");

		      	return $this->redirect($this->generateUrl('cm_platform_list'));

        	}
        	else{
        		$repository = $em->getRepository('CMPlatformBundle:LinkUserClient');
				$clientLink = $repository->findBy(array('userClientId' => $id));
				if(count($clientLink) > 1){
					$clientLinkForRemove = $repository->findOneBy(array('userId' => $user, 'userClientId' => $id));
					$em->remove($clientLinkForRemove);

			      	$em->flush();

			      	$request->getSession()->getFlashBag()->add('info', "Le client a bien été supprimée.");

			      	return $this->redirect($this->generateUrl('cm_platform_list'));
				}
				else{
					$em->remove($clientLink);
			      	$em->remove($client);

			      	$em->flush();

			      	$request->getSession()->getFlashBag()->add('info', "Le client a bien été supprimée.");

			      	return $this->redirect($this->generateUrl('cm_platform_list'));
				}
        	}

			
	    }

	    return $this->render('CMPlatformBundle:Platform:delete.html.twig', array(
	      'client' => $client,
	      'form'   => $form->createView()
	    ));
    }

    public function editAction(Request $request, $id)
    {
    	if (!$this->get('security.context')->isGranted('ROLE_USER')) {
	      throw new AccessDeniedException('Accès limité aux utilisateurs enregistrés.');
	    }

    	$client = new User();

		$client = $this->getDoctrine()
		  	->getManager()
		  	->getRepository('CMUserBundle:User')
		  	->find($id)
		;

		if (null === $client) {
	      	return $this->render('CMPlatformBundle:Platform:listNoClient.html.twig');
	    }
	    
		$form = $this->get('form.factory')->createBuilder('form', $client)
	    	->add('nom',         'text')
	      	->add('prenom',      'text')
	      	->add('email',       'email')
	      	->add('adresse',     'text')
	      	->add('telephone',   'text')
	      	->add('web_site',    'text')
      		->add('sauvegarder', 'submit')
	      	->getForm()
	    ;

	    $form->handleRequest($request);

	    if ($form->isValid()) {
	      	$em = $this->getDoctrine()->getManager();
	      	$em->persist($client);
	      	$em->flush();

	      	$request->getSession()->getFlashBag()->add('notice', 'Client bien modifié.');

	      	return $this->redirect($this->generateUrl('cm_platform_view', array(
	      		'id' => $client->getId())));
	    }

	    return $this->render('CMPlatformBundle:Platform:edit.html.twig', array(
	      	'form'   => $form->createView(),
	      	'id'     => $id,
	      	'client' => $client,
	    ));
    }

	public function searchemailAction()
	{               
	    $request = $this->container->get('request');

	    if($request->isXmlHttpRequest())
	    {
	        $motcle = '';
	        $motcle = $request->request->get('motcle');

	        $em = $this->container->get('doctrine')->getEntityManager();

	        if($motcle != '')
	        {
	        	$repository = $this
		  			->getDoctrine()
		  			->getManager()
		  			->getRepository('CMUserBundle:User')
				;

				$client = $repository->findOneByEmail(array('email' => $motcle));
	        }

	        if (null === $client) {
	        	$response = $this->render('CMPlatformBundle:Platform:listClientForRegister.html.twig', array('client' => $client));
				// set 404 status code
				$response->setStatusCode(500);
				// send all to the browser
				return $response;
	    	}

			return $this->container->get('templating')->renderResponse('CMPlatformBundle:Platform:listClientForRegister.html.twig', array(
				'client' => $client
	        ));
	    }
	}

	public function searchidAction()
	{               
	    $request = $this->container->get('request');

	    if($request->isXmlHttpRequest())
	    {
	        $id = '';
	        $id = $request->request->get('id');

	        $em = $this->container->get('doctrine')->getEntityManager();

	        if($id != '')
	        {
	        	$repository = $this
		  			->getDoctrine()
		  			->getManager()
		  			->getRepository('CMUserBundle:User')
				;

				$client = $repository->findOneByid($id);
	        }
	    	$response = new Response();
	        $clientinfo = json_encode(array(
	        	'nom' =>$client->getNom(), 
	        	'prenom' =>$client->getPrenom(), 
	        	'adresse' =>$client->getAdresse(), 
	        	'telephone' =>$client->getTelephone(), 
	        	'website' =>$client->getWebSite()
	        ));
	        $response->headers->set('Content-Type', 'application/json');
	        $response->setContent($clientinfo);
	        return $response;
	    }
	}

	public function updateidAction()
	{               
	    $request = $this->container->get('request');

	    if($request->isXmlHttpRequest())
	    {
	        $id = $request->request->get('id');
	        $username = $request->request->get('username');
	        $password = $request->request->get('password');

	        if($id != '')
	        {
		      	$em = $this->getDoctrine()->getManager();

				$userUpdate = $em->getRepository('CMUserBundle:User')->find($id);

				if(($userUpdate->isEnabled()) == 0){
					$factory = $this->get('security.encoder_factory');


					$userUpdate->setUsername($username);

					$salt = $userUpdate->getSalt();
					$iterations = 5000; // Par défaut
					$result = '';
					$salted = $password.'{'.$salt.'}';
					 
					$digest = hash('sha512', $salted, true);
					 
					for ($i = 1; $i < $iterations; $i++) {
					    $digest = hash('sha512', $digest.$salted, true);
					}
					$cryptedPass = base64_encode($digest);

					$userUpdate->setPassword($cryptedPass);
					$userUpdate->setEnabled('1');
					$this->get('fos_user.user_manager')->updateUser($userUpdate,true);

			        $this->getDoctrine()->getManager()->flush();
			        $clientinfo = 'Success';
				}
				else{
					$clientinfo = 'ErrorClientRegister';
				}
		    }
	    	$response = new Response();
	        $response->setContent($clientinfo);
	        return $response;
	    }
	}
	
}