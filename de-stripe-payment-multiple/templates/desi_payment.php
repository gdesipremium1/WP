<?php
/*
  Plugin Name: formulaire de payement desi
  Plugin URI: 
  Description: formulaire de payement desi
  Version: 1.0.0
  Author: 
  Author URI: 
*/
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<script src="https://js.stripe.com/v3/"></script>
	<title>Page de paymen</title>
</head>

<body>
	

	<script>
	    const stripe = Stripe('pk_test_51NqumbJCzpIre4CkIZGDxlf1QKfG7KZXq71UJ47Z4dD1T7tSZg5SlOV5nBBd79H0puCT60mVWcg5HZffKpTpFyXjh53MCq00k0kpZVgK');

        const appearance = {
          theme: 'flat',
          variables: { colorPrimaryText: '#262626' }
        };
        const options = { mode: 'shipping' };
        const elements = stripe.elements({ clientSecret, appearance });
        const linkAuthElement = elements.create('linkAuthentication');
        const addressElement = elements.create('address', options);
        const paymentElement = elements.create('payment');
        linkAuthElement.mount('#link-auth-element');
        addressElement.mount('#address-element');
        paymentElement.mount('#payment-element');
	</script>
	<script src="<?php echo DE_STRIPE_PAY_MULTIPLE_URI . 'public/js/function.js' ?>"></script>
	<script src="<?php echo DE_STRIPE_PAY_MULTIPLE_URI . 'public/js/action.js' ?>"></script>
</body>

</html>