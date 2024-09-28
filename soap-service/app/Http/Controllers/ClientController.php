<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Entities\Client;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    protected $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    public function register(Request $request)
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
            'phone' => 'required|unique:App\Entities\Client,phone',
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
                'message_error' => $th->getMessage(),
                'data' => null
            ];
        }
    }

    public function charge(Request $request)
    {
        $document = $request->document;
        $phone = $request->phone;
        $amount = $request->amount;

        $validator = Validator::make([
            'document' =>  $document,
            'phone' => $phone,
            'amount' => $amount,
        ], [
            'document' => 'required',
            'phone' => 'required',
            'amount' => 'required|numeric|gt:0',
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'cod_error' => '400',
                'message_error' => $validator->errors()->getMessages(),
                'data' => null
            ];
        }

        $qb = $this->em->createQueryBuilder();
        try {
            $results = $qb->select('c')
                ->from('App\Entities\Client', 'c')
                ->where('c.document = ?0')
                ->andWhere('c.phone = ?1')
                ->setParameter(0, $document)
                ->setParameter(1, $phone)
                ->getQuery()
                ->getResult();

            if (count($results) <= 0) {
                return [
                    'success' => false,
                    'cod_error' => '404',
                    'message_error' => 'Client not found',
                    'data' => null
                ];
            }

            $client = $results[0];
            
            $client->addBalance($amount);

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
                'message_error' => $th->getMessage(),
                'data' => null
            ];
        }
    }
}
