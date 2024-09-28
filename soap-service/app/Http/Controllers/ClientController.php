<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Entities\Client;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    protected $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    public function store(Request $request)
    {
        $document = $request->document;
        $email = $request->email;
        $phone = $request->phone;
        $name = $request->name;

        $validator = Validator::make([
            'document' =>  $document,
            'email' => $email,
            'phone' => $phone,
            'name' => $name,
        ], [
            'document' => 'required|unique:App\Entities\Client,document',
            'email' => 'required|email|unique:App\Entities\Client,email',
            'phone' => 'required',
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'cod_error' => '400',
                'message_error' => $validator->errors()->getMessages(),
                'data' => null
            ];
        }


        try {

            $client = new Client(
                $document,
                $email,
                $phone,
                name: $name,
            );


            $this->em->persist($client);
            $this->em->flush();
            

            return [
                'success' => true,
                'cod_error' => '00',
                'message_error' => null,
                'data' => null
            ];

        } catch (\Throwable $th) {
            return [
                'success' => false,
                'cod_error' => '500',
                'message_error' => 'Internal server error',
                'data' => null
            ];
        }
    }
}
