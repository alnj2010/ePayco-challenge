<?php

namespace App\Http\Controllers;

use App\Entities\Client;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;
use App\Mail\PurchaseMail;
use Illuminate\Support\Facades\Log;

use Illuminate\Validation\ValidationException;

class ClientController
{
    protected $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Register a Client
     *
     * @param string $document
     * @param string $email
     * @param string $phone
     * @param string $name
     * @return array{
     *  success:boolean,
     *  cod_error:string,
     *  message_error:string,
     *  data:string,
     * }
     */
    public function register($document, $email, $phone, $name)
    {
        Log::debug($document);
        Log::debug($email);
        Log::debug($phone);
        Log::debug($name);

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
                'message_error' =>  implode(",",$validator->messages()->all()),
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

    /**
     * Deposit money in a client's wallet
     *
     * @param string $document
     * @param string $phone
     * @param float $amount
     * @return array{
     *  success:boolean,
     *  cod_error:string,
     *  message_error:string,
     *  data:string,
     * }
     */

    public function charge($document, $phone, $amount)
    {

        Log::debug($document);
        Log::debug($phone);
        Log::debug($amount);

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
                'message_error' => implode(",",$validator->messages()->all()),
                'data' => null
            ];
        }

        try {
            $client = $this->getClientByDocumentAndPhone($document, $phone);

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
            if ($th instanceof ValidationException) {

                return [
                    'success' => false,
                    'cod_error' => $th->errors()["cod_error"][0],
                    'message_error' => $th->errors()["message_error"][0],
                    'data' => null
                ];
            }
            return [
                'success' => false,
                'cod_error' => '500',
                'message_error' => $th->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Check Client's current balance
     *
     * @param string $document
     * @param string $phone
     * @return array{
     *  success:boolean,
     *  cod_error:string,
     *  message_error:string,
     *  data:string,
     * }
     */
    public function check_balance($document, $phone)
    {
        Log::debug($document);
        Log::debug($phone);

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
                'message_error' => implode(",",$validator->messages()->all()),
                'data' => null
            ];
        }

        try {
            $client = $this->getClientByDocumentAndPhone($document, $phone);

            return [
                'success' => true,
                'cod_error' => '00',
                'message_error' => null,
                'data' => 'Your balance is ' . $client->getBalance() . '$'
            ];
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) {

                return [
                    'success' => false,
                    'cod_error' => $th->errors()["cod_error"][0],
                    'message_error' => $th->errors()["message_error"][0],
                    'data' => null
                ];
            }
            return [
                'success' => false,
                'cod_error' => '500',
                'message_error' => $th->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Purchase a product
     *
     * @param string $document
     * @param string $phone
     * @param float $price
     * @return array{
     *  success:boolean,
     *  cod_error:string,
     *  message_error:string,
     *  data:string,
     * }
     */
    public function purchase($document, $phone, $price)
    {
        Log::debug($document);
        Log::debug($price);
        Log::debug($phone);

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
                'message_error' => implode(",",$validator->messages()->all()),
                'data' => null
            ];
        }

        try {
            $client = $this->getClientByDocumentAndPhone($document, $phone);

            $balance = $client->getBalance();

            if ($balance < $price) {
                return [
                    'success' => false,
                    'cod_error' => '400',
                    'message_error' => 'Insufficient balance',
                    'data' => null
                ];
            }

            $token = $this->generate_6digit_token();
            session()->put('token', $token);
            session()->put('price', $price);
            session()->put('document', $document);
            session()->put('phone', $phone);

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
            if ($th instanceof ValidationException) {

                return [
                    'success' => false,
                    'cod_error' => $th->errors()["cod_error"][0],
                    'message_error' => $th->errors()["message_error"][0],
                    'data' => null
                ];
            }
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
        $url = env('REST_APP_VERIFY_URL') . '?verify=' . $crypted;
        return $url;
    }

    private function generate_6digit_token()
    {
        $six_digit_random_number = (string)random_int(100000, 999999);
        return $six_digit_random_number;
    }
    /**
     * Confirm purchase
     *
     * @param string $verify_token
     * @return array{
     *  success:boolean,
     *  cod_error:string,
     *  message_error:string,
     *  data:string,
     * }
     */
    public function confirm($verify_token)
    {

        $validator = Validator::make([
            'verify_token' =>  $verify_token,
        ], [
            'verify_token' => 'required',
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'cod_error' => '400',
                'message_error' => implode(",",$validator->messages()->all()),
                'data' => null
            ];
        }
        try {
            $value = $this->verifyToken($verify_token);

            $client = $this->getClientByDocumentAndPhone($value['document'], $value['phone']);
            $balance = $client->getBalance();

            if ($balance < $value['price']) {
                return [
                    'success' => false,
                    'cod_error' => '400',
                    'message_error' => 'Insufficient balance',
                    'data' => null
                ];
            }

            $client->discountProduct($value['price']);

            $this->em->persist($client);
            $this->em->flush();

            return [
                'success' => true,
                'cod_error' => '00',
                'message_error' => null,
                'data' => 'purchase success'
            ];
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) {

                return [
                    'success' => false,
                    'cod_error' => $th->errors()["cod_error"][0],
                    'message_error' => $th->errors()["message_error"][0],
                    'data' => null
                ];
            }
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
        $remote_payload = Crypt::decrypt($verifyToken);
        $validator = Validator::make($remote_payload, [
            'id' => 'required',
            'token' => 'required',
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages(['message_error' => 'Invalid token', 'cod_error' => '400']);
        }

        $session_payload = unserialize(session()->getHandler()->read($remote_payload["id"]));
        if (!$session_payload) {
            throw ValidationException::withMessages(['message_error' => 'Invalid token', 'cod_error' => '400']);
        }
        $validator = Validator::make($session_payload, [
            'price' => 'required',
            'document' => 'required',
            'phone' => 'required',
        ]);

        if ($validator->fails() || $remote_payload["token"] != $session_payload["token"]) {
            throw ValidationException::withMessages(['message_error' => 'Invalid token', 'cod_error' => '400']);
        }

        session()->getHandler()->destroy($remote_payload["id"]);

        return [
            'price' => $session_payload["price"],
            'document' => $session_payload["document"],
            'phone' => $session_payload["phone"],
        ];
    }

    private function getClientByDocumentAndPhone($document, $phone)
    {
        $qb = $this->em->createQueryBuilder();

        $results = $qb->select('c')
            ->from('App\Entities\Client', 'c')
            ->where('c.document = ?0')
            ->andWhere('c.phone = ?1')
            ->setParameter(0, $document)
            ->setParameter(1, $phone)
            ->getQuery()
            ->getResult();

        if (count($results) <= 0) {
            throw ValidationException::withMessages(['message_error' => 'Client not found', 'cod_error' => '404']);
        }
        return $results[0];
    }
}
