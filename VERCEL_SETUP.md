# Vercel Deployment Setup Guide

## API Keys Required

Based on your website's configuration, you need the following API keys:

### 1. OpenAI API Key
- **Where to get**: [OpenAI API Dashboard](https://platform.openai.com/api-keys)
- **Format**: Starts with `sk-`
- **Usage**: Powers the chatbot functionality in `chat.php`

### 2. Firebase API Keys
- **Where to get**: [Firebase Console](https://console.firebase.google.com/)
- **Required keys**:
  - Web API Key (starts with `AIzaSy`)
  - FCM Server Key (for push notifications)
  - Project ID
  - Messaging Sender ID
  - App ID

## Setting Up Environment Variables in Vercel

### Step 1: Access Vercel Dashboard
1. Go to [vercel.com](https://vercel.com)
2. Log in to your account
3. Navigate to your project
4. Click on **Settings** tab
5. Click on **Environment Variables** in the left sidebar

### Step 2: Add Required Environment Variables

Add the following environment variables:

#### OpenAI Configuration
```
OPENAI_API_KEY = [Your OpenAI API Key]
OPENAI_MODEL = gpt-3.5-turbo
OPENAI_MAX_TOKENS = 150
OPENAI_TEMPERATURE = 0.7
```

#### Firebase Configuration
```
FIREBASE_API_KEY = [Your Firebase Web API Key]
FIREBASE_SERVER_KEY = [Your FCM Server Key]
FIREBASE_PROJECT_ID = [Your Firebase Project ID]
FIREBASE_MESSAGING_SENDER_ID = [Your Messaging Sender ID]
FIREBASE_APP_ID = [Your Firebase App ID]
FIREBASE_MEASUREMENT_ID = [Your GA Measurement ID]
```

#### Chatbot Configuration (Optional)
```
CHATBOT_SYSTEM_PROMPT = You are a helpful assistant for child adoption in the Philippines.
CHATBOT_FALLBACK_MESSAGE = I apologize, but I'm having trouble processing your request right now. Please try again later.
```

### Step 3: Environment Settings
For each environment variable:
- **Environment**: Select `Production`, `Preview`, and `Development`
- **Value**: Enter your actual API key/value
- Click **Save**

## Local Development Setup

For local development, create a `config.json` file in your root directory:

```json
{
  "openai": {
    "api_key": "sk-your-openai-api-key-here",
    "model": "gpt-3.5-turbo",
    "max_tokens": 150,
    "temperature": 0.7
  },
  "firebase": {
    "apiKey": "AIzaSy-your-firebase-api-key-here",
    "serverKey": "your-firebase-server-key-here",
    "projectId": "your-firebase-project-id",
    "messagingSenderId": "your-messaging-sender-id",
    "appId": "your-firebase-app-id",
    "measurementId": "your-measurement-id"
  },
  "chatbot": {
    "system_prompt": "You are a helpful assistant for child adoption in the Philippines.",
    "fallback_message": "I apologize, but I'm having trouble processing your request right now. Please try again later."
  }
}
```

**Note**: Never commit `config.json` to version control. Add it to your `.gitignore` file.

## Deployment Steps

1. **Push your code** to GitHub/GitLab/Bitbucket
2. **Connect repository** to Vercel
3. **Add environment variables** as described above
4. **Deploy** your project

## Troubleshooting

### Common Issues:
1. **API Key not found**: Ensure environment variables are set for all environments
2. **Permission denied**: Check that your API keys have the correct permissions
3. **Rate limiting**: Monitor your API usage and upgrade plans if needed

### Testing Your Setup:
1. Check the `/chat.php` endpoint
2. Monitor Vercel function logs
3. Test with a simple message to verify OpenAI integration

## Security Notes
- Never expose API keys in client-side code
- Use environment variables for all sensitive data
- Regularly rotate your API keys
- Monitor usage and set up billing alerts

## Files Modified for Vercel Compatibility:
- `chat.php` - Updated to use environment variables
- `config_helper.php` - New configuration helper
- `vercel.json` - Deployment configuration
- `config.template.json` - Template for local development 