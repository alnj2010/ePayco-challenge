<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Entities\Client;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;
use App\Mail\PurchaseMail;
use Ramsey\Uuid\Provider\Node\RandomNodeProvider;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Session;

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
                'data' => 'success charge'
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

    public function check_balance(Request $request)
    {
        $document = $request->document;
        $phone = $request->phone;

        $validator = Validator::make([
            'document' =>  $document,
            'phone' => $phone
        ], [
            'document' => 'required',
            'phone' => 'required',
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

            return [
                'success' => true,
                'cod_error' => '00',
                'message_error' => null,
                'data' => 'Your balance is ' . $client->getBalance() . '$'
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

    public function purchase(Request $request)
    {
        $document = $request->document;
        $phone = $request->phone;
        $price = $request->price;

        $validator = Validator::make([
            'document' =>  $document,
            'phone' => $phone,
            'price' => $price
        ], [
            'document' => 'required',
            'phone' => 'required',
            'price' => 'required|numeric|gt:0',

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

            $balance = $client->getBalance();

            if ($balance < $price) {
                return [
                    'success' => false,
                    'cod_error' => '400',
                    'message_error' => 'Insufficient balance',
                    'data' => null
                ];
            }

            $token = $this->create_uuid();
            session([
                'token' => $token,
                'price' => $price,
                'document' => $document,
                'phone' => $phone
            ]);
            $id = session()->getId();


            $purchase = [
                'name' => $client->getName(),
                'confirm_url' => $this->createVerificationUrl($id, $token),
                'price' => $price
            ];

            Mail::to($client->getEmail())->send(new PurchaseMail($purchase));

            return [
                'success' => true,
                'cod_error' => '00',
                'message_error' => null,
                'data' => 'Confirmation email has been sent'
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
    private function createVerificationUrl($id, $token)
    {
        $dataToken = ['id' => $id, 'token' => $token];
        $crypted = Crypt::encrypt($dataToken);
        $url = env('APP_URL') . '/api/confirm-purchase/' . '?verify=' . $crypted;
        return $url;
    }

    private function create_uuid()
    {
        $nodeProvider = new RandomNodeProvider();
        $clockSequence = 16383;
        return Uuid::uuid1($nodeProvider->getNode(), $clockSequence);
    }
    public function confirm(Request $request)
    {
        $verify_token =  $request->query(key: 'verify');

        $validator = Validator::make([
            'verify_token' =>  $verify_token,
        ], [
            'verify_token' => 'required',
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'cod_error' => '400',
                'message_error' => $validator->errors()->getMessages(),
                'data' => null
            ];
        }

        $payload = $this->verifyToken($verify_token);

        try {
            return $payload;
        } catch (\Throwable $th) {
            return [
                'success' => false,
                'cod_error' => '500',
                'message_error' => $th->getMessage(),
                'data' => null
            ];
        }
    }



    private function verifyToken($verifyToken)
    {
        $decrypt = Crypt::decrypt($verifyToken);
        return $decrypt;
        $id = $decrypt["id"];
        $remote_token = $decrypt["token"];
    }
}
