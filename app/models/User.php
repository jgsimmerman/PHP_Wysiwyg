<?php

namespace Altum\Models;

use Altum\Database\Database;

class User extends Model {

    public function get($user_id) {

        $data = Database::get('*', 'users', ['user_id' => $user_id]);

        if($data) {

            /* Parse the users package settings */
            $data->package_settings = json_decode($data->package_settings);

        }

        return $data;
    }

    public function delete($user_id) {

        /* Cancel his active subscriptions if active */
        $this->cancel_subscription($user_id);

        /* Delete the record from the database */
        Database::$database->query("DELETE FROM `users` WHERE `user_id` = {$user_id}");

        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $user_id);

    }

    public function update_last_activity($user_id) {

        Database::update('users', ['last_activity' => \Altum\Date::$date], ['user_id' => $user_id]);

    }

    /*
     * Needs to have access to the Settings and the User variable, or pass in the user_id variable
     */
    public function cancel_subscription($user_id = false) {

        if(!isset($this->settings)) {
            throw new \Exception('Model needs to have access to the "settings" variable.');
        }

        if(!isset($this->user) && !$user_id) {
            throw new \Exception('Model needs to have access to the "user" variable or pass in the $user_in.');
        }

        if($user_id) {
            $this->user = Database::get(['user_id', 'payment_subscription_id'], 'users', ['user_id' => $user_id]);
        }

        if(empty($this->user->payment_subscription_id)) {
            return true;
        }

        $data = explode('###', $this->user->payment_subscription_id);
        $type = $data[0];
        $subscription_id = $data[1];

        switch($type) {
            case 'STRIPE':

                /* Initiate Stripe */
                \Stripe\Stripe::setApiKey($this->settings->stripe->secret_key);

                /* Cancel the Stripe Subscription */
                $subscription = \Stripe\Subscription::retrieve($subscription_id);
                $subscription->cancel();

                break;

            case 'PAYPAL':

                /* Initiate paypal */
                $paypal = new \PayPal\Rest\ApiContext(new \PayPal\Auth\OAuthTokenCredential($this->settings->paypal->client_id, $this->settings->paypal->secret));
                $paypal->setConfig(['mode' => $this->settings->paypal->mode]);

                /* Create an Agreement State Descriptor, explaining the reason to suspend. */
                $agreement_state_descriptior = new \PayPal\Api\AgreementStateDescriptor();
                $agreement_state_descriptior->setNote('Suspending the agreement');

                /* Get details about the executed agreement */
                $agreement = \PayPal\Api\Agreement::get($subscription_id, $paypal);

                /* Suspend */
                $agreement->suspend($agreement_state_descriptior, $paypal);


                break;
        }

        Database::$database->query("UPDATE `users` SET `payment_subscription_id` = '' WHERE `user_id` = {$this->user->user_id}");

    }

}
