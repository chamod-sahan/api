/**
 * RideFlexi Payment API Integration Example
 * Use this code in your Expo React Native app
 */

// Example 1: Create Payment Session
async function createPaymentSession(amount, customerData) {
  try {
    const response = await fetch('http://yourserver.com/api.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-API-Key': 'RF2024SecureKey' // Your API passcode
      },
      body: JSON.stringify({
        amount: amount,
        customer_name: customerData.name,
        customer_email: customerData.email,
        customer_phone: customerData.phone,
        order_description: 'Premium Ride + Insurance' // Optional
      })
    });

    const result = await response.json();

    if (result.success) {
      console.log('Payment URL:', result.data.payment_url);
      console.log('Order ID:', result.data.order_id);
      
      // Open payment page in browser or WebView
      return result.data;
    } else {
      console.error('Error:', result.message);
      return null;
    }
  } catch (error) {
    console.error('Network error:', error);
    return null;
  }
}

// Example 2: Usage in React Native Component
import React, { useState } from 'react';
import { View, Button, Linking } from 'react-native';

function PaymentScreen() {
  const [loading, setLoading] = useState(false);

  const handlePayment = async () => {
    setLoading(true);

    const paymentData = await createPaymentSession(2500, {
      name: 'John Doe',
      email: 'john@example.com',
      phone: '0771234567'
    });

    if (paymentData) {
      // Open payment page in device browser
      Linking.openURL(paymentData.payment_url);
      
      // OR use WebView to keep user in app
      // navigation.navigate('PaymentWebView', { url: paymentData.payment_url });
    }

    setLoading(false);
  };

  return (
    <View>
      <Button 
        title={loading ? "Processing..." : "Pay Now"}
        onPress={handlePayment}
        disabled={loading}
      />
    </View>
  );
}

// Example 3: With WebView (recommended)
import { WebView } from 'react-native-webview';

function PaymentWebView({ route }) {
  const { url } = route.params;

  const handleNavigationStateChange = (navState) => {
    // Check if payment is complete
    if (navState.url.includes('success.php')) {
      // Payment successful
      console.log('Payment completed!');
      // Navigate back to app
    } else if (navState.url.includes('cancel.php')) {
      // Payment cancelled
      console.log('Payment cancelled');
    }
  };

  return (
    <WebView
      source={{ uri: url }}
      onNavigationStateChange={handleNavigationStateChange}
    />
  );
}

// Example 4: Alternative - Direct checkout URL (without API)
function directPayment() {
  const amount = 2500;
  const checkoutUrl = `http://yourserver.com/checkout.php?amount=${amount}&name=John Doe&email=john@example.com&phone=0771234567`;
  
  Linking.openURL(checkoutUrl);
}

/**
 * API AUTHENTICATION OPTIONS:
 * 
 * Option 1: Using X-API-Key header (Recommended)
 * headers: { 'X-API-Key': 'RF2024SecureKey' }
 * 
 * Option 2: Using Authorization Bearer token
 * headers: { 'Authorization': 'Bearer RF2024SecureKey' }
 * 
 * Option 3: Using URL parameter
 * fetch('http://yourserver.com/api.php?api_key=RF2024SecureKey', ...)
 */

/**
 * CONFIGURATION:
 * 1. Replace 'yourserver.com' with your actual server URL
 * 2. Replace 'RF2024SecureKey' with your actual API passcode (from api.php line 18)
 * 3. Update return URLs in checkout.php (lines 29-31)
 */

export { createPaymentSession, PaymentScreen, PaymentWebView, directPayment };
