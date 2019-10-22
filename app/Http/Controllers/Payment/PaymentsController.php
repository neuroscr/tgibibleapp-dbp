<?php

namespace App\Http\Controllers\Payment;

use App\Traits\AccessControlAPI;
use App\Http\Controllers\APIController;
use App\Traits\CheckProjectMembership;
use Illuminate\Http\Request;
use Stripe\Charge;
use Stripe\Exception\RateLimitException;
use Stripe\Exception\InvalidRequestException;
use Stripe\Exception\AuthenticationException;
use Stripe\Exception\ApiConnectionException;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\CardException;
use Stripe\Stripe;

class PaymentsController extends APIController
{
    use AccessControlAPI;
    use CheckProjectMembership;

    /**
     *
     * @OA\Get(
     *     path="/payments/{payment_id}",
     *     tags={"Payments"},
     *     summary="A stripe payment object",
     *     operationId="v4_payments.show",
     *     security={{"api_token":{}}},
     *     @OA\Parameter(name="payment_id",
     *         in="path",
     *         required=true,
     *         description="The stripe payment id",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, ref="#/components/responses/payment")
     * )
     *
     * @OA\Response(
     *   response="payment",
     *   description="Payment Object",
     *   @OA\MediaType(mediaType="application/json", @OA\Schema(ref="#/components/schemas/v4_payment_detail")),
     *   @OA\MediaType(mediaType="application/xml",  @OA\Schema(ref="#/components/schemas/v4_payment_detail")),
     *   @OA\MediaType(mediaType="text/x-yaml",      @OA\Schema(ref="#/components/schemas/v4_payment_detail")),
     *   @OA\MediaType(mediaType="text/csv",         @OA\Schema(ref="#/components/schemas/v4_payment_detail"))
     * )
     *
     * @OA\Schema (
     *     type="object",
     *     schema="v4_payment_detail",
     *     description="v4_payment_detail",
     *     title="v4_payment_detail",
     *     @OA\Property(property="id", type="string"),
     *     @OA\Property(property="amount", type="integer"),
     *     @OA\Property(property="captured", type="boolean"),
     *     @OA\Property(property="currency", type="string"),
     *     @OA\Property(property="paid", type="boolean")
     * )
     *
     */
    public function show(Request $request, $payment_id)
    {
        $user = $request->user();
        // Validate Project / User Connection
        if (!empty($user) && !$this->compareProjects($user->id, $this->key)) {
            return $this->setStatusCode(401)->replyWithError(trans('api.projects_users_not_connected'));
        }

        Stripe::setApiKey(config('payments.stripeSecretKey'));

        try {
            $charge = Charge::retrieve($payment_id);
        } catch (InvalidRequestException $e) {
            return $this->setStatusCode($e->getHttpStatus())->replyWithError($e->getError()->message);
        } catch (AuthenticationException $e) {
            return $this->setStatusCode($e->getHttpStatus())->replyWithError($e->getError()->message);
        } catch (ApiConnectionException $e) {
            return $this->setStatusCode($e->getHttpStatus())->replyWithError('The connection with the payment system failed, please try again later');
        } catch (ApiErrorException $e) {
            return $this->setStatusCode($e->getHttpStatus())->replyWithError('The payment system failed, please try again later');
        } catch (Exception $e) {
            return $this->setStatusCode($e->getHttpStatus())->replyWithError($e->getError()->message);
        }

        return $this->reply($charge);
    }

    /**
     *
     * @OA\Post(
     *     path="/payments",
     *     tags={"Payments"},
     *     summary="Make a payment to stripe",
     *     operationId="v4_payments.charge",
     *     security={{"api_token":{}}},
     *     @OA\RequestBody(required=true, description="Fields for making a payment",
     *           @OA\MediaType(mediaType="application/json",
     *              @OA\Schema(
     *                  required={"amount","payment_token"},
     *                  @OA\Property(property="amount", type="integer", description="A positive integer representing how much to charge in the smallest currency unit (e.g., 100 cents to charge $1.00 or 100 to charge ¥100, a zero-decimal currency). The minimum amount is $0.50 US or equivalent in charge currency. The amount value supports up to eight digits (e.g., a value of 99999999 for a USD charge of $999,999.99)."),
     *                  @OA\Property(property="currency", type="string", default="usd", description="Three-letter ISO currency code, in lowercase."),
     *                  @OA\Property(property="payment_token", type="string", description="A payment token to be charged."),
     *                  @OA\Property(property="description", type="string", description="An arbitrary string which you can attach to a Charge object.")
     *              )
     *          )
     *     ),
     *     @OA\Response(response=200, ref="#/components/responses/payment")
     * )
     *
     */
    public function charge(Request $request)
    {
        $user = $request->user();
        // Validate Project / User Connection
        if (!empty($user) && !$this->compareProjects($user->id, $this->key)) {
            return $this->setStatusCode(401)->replyWithError(trans('api.projects_users_not_connected'));
        }

        $amount = checkParam('amount', true);
        $currency = checkParam('currency') ?? 'usd';
        $source = checkParam('payment_token', true);
        $description = checkParam('description');

        Stripe::setApiKey(config('payments.stripeSecretKey'));


        try {
            $charge = Charge::create([
                'amount' => $amount,
                'currency' => $currency,
                'source' => $source,
                'description' => $description,
                'metadata' => ['user_id' => $user->id]
            ]);
        } catch (CardException $e) {
            return $this->setStatusCode($e->getHttpStatus())->replyWithError($e->getError()->message);
        } catch (RateLimitException $e) {
            return $this->setStatusCode($e->getHttpStatus())->replyWithError($e->getError()->message);
        } catch (InvalidRequestException $e) {
            return $this->setStatusCode($e->getHttpStatus())->replyWithError($e->getError()->message);
        } catch (AuthenticationException $e) {
            return $this->setStatusCode($e->getHttpStatus())->replyWithError($e->getError()->message);
        } catch (ApiConnectionException $e) {
            return $this->setStatusCode($e->getHttpStatus())->replyWithError('The connection with the payment system failed, please try again later');
        } catch (ApiErrorException $e) {
            return $this->setStatusCode($e->getHttpStatus())->replyWithError('The payment system failed, please try again later');
        } catch (Exception $e) {
            return $this->setStatusCode($e->getHttpStatus())->replyWithError($e->getError()->message);
        }

        return $this->reply($charge);
    }
}
