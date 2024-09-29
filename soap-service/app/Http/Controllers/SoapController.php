<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laminas\Soap\AutoDiscover as WsdlAutoDiscover;
use Laminas\Soap\Server as SoapServer;
use Doctrine\ORM\EntityManagerInterface;

class SoapController extends Controller
{

    protected $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    public function wsdlAction(Request $request)
    {
        if (!$request->isMethod('get')) {
            return $this->prepareClientErrorResponse('GET');
        }

        $wsdl = new WsdlAutoDiscover();

        $wsdl->setUri(route('soap-server'))
            ->setServiceName('MySoapService');


        $this->populateServer($wsdl);

        return response()->make($wsdl->toXml())
            ->header('Content-Type', 'application/xml');
    }

    public function serverAction(Request $request)
    {
        if (!$request->isMethod('post')) {
            return $this->prepareClientErrorResponse('POST');
        }

        $context = stream_context_create(array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )

        ));

        $soapClientOptions = array(
            'stream_context' => $context,
            'actor' => route('soap-server'),
            'soap_version' => SOAP_1_2,
            'uri' => route('soap-wsdl')
        );

        $server = new SoapServer(
            null,
            $soapClientOptions
        );

        $server->setReturnResponse(true);
        $clientController = new ClientController($this->em);
        $server->setObject($clientController);
        $soapResponse = $server->handle();

        return response()->make($soapResponse)->header('Content-Type', 'application/xml');
    }

    private function prepareClientErrorResponse($allowed)
    {
        return response()->make('Method not allowed', 405)->header('Allow', $allowed);
    }

    private function populateServer($server)
    {
        // Expose a class and its methods:
        $server->setClass(ClientController::class);

        // Expose an object instance and its methods:
        //$clientController = new ClientController($this->em);
        //$server->setObject($clientController);

        // Expose a function:
        // $server->addFunction('Acme\Model\ping');
    }
}
