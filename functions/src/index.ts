import * as functions from "firebase-functions/v1";
import * as admin from "firebase-admin";

admin.initializeApp();

// Simple HTTP endpoint to handle email verification
export const handleEmailVerification = functions.https.onRequest(async (req, res) => {
  // Set CORS headers
  res.set('Access-Control-Allow-Origin', '*');
  res.set('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
  res.set('Access-Control-Allow-Headers', 'Content-Type');

  if (req.method === 'OPTIONS') {
    res.status(204).send('');
    return;
  }

  if (req.method !== 'POST') {
    res.status(405).json({ error: 'Method not allowed' });
    return;
  }

  const { uid } = req.body;

  if (!uid) {
    res.status(400).json({ error: 'User ID is required' });
    return;
  }

  try {
    // Get user from Firebase Auth to check verification status
    const authUser = await admin.auth().getUser(uid);
    
    if (authUser.emailVerified) {
      // Update Firestore document
      const userRef = admin.firestore().collection("users").doc(uid);
      
      // Check if document exists first
      const userDoc = await userRef.get();
      if (!userDoc.exists) {
        // Create the document if it doesn't exist
        await userRef.set({
          email: authUser.email,
          emailVerified: true,
          isVerified: true,
          verifiedAt: admin.firestore.FieldValue.serverTimestamp(),
          createdAt: admin.firestore.FieldValue.serverTimestamp(),
          role: 'user'
        });
        console.log(`Created new user document for verified user ${uid}`);
      } else {
        // Update existing document
        await userRef.update({
          emailVerified: true,
          isVerified: true,
          verifiedAt: admin.firestore.FieldValue.serverTimestamp(),
          lastUpdated: admin.firestore.FieldValue.serverTimestamp()
        });
        console.log(`Updated verification status for user ${uid}`);
      }
      
      res.status(200).json({
        success: true,
        message: 'Email verification status updated successfully',
        verified: true
      });
    } else {
      res.status(400).json({
        success: false,
        message: 'Email is not verified in Firebase Auth',
        verified: false
      });
    }
    
  } catch (error: any) {
    console.error(`Error handling email verification for user ${uid}:`, error);
    res.status(500).json({
      error: 'Internal server error',
      message: error.message
    });
  }
});

// HTTP function to manually trigger email verification fix
export const fixEmailVerification = functions.https.onRequest(async (req, res) => {
  // Set CORS headers
  res.set('Access-Control-Allow-Origin', '*');
  res.set('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
  res.set('Access-Control-Allow-Headers', 'Content-Type');

  if (req.method === 'OPTIONS') {
    res.status(204).send('');
    return;
  }

  if (req.method !== 'POST') {
    res.status(405).json({ error: 'Method not allowed' });
    return;
  }

  const { userId, email } = req.body;

  if (!userId && !email) {
    res.status(400).json({ error: 'Either userId or email is required' });
    return;
  }

  try {
    let userDoc;
    let userRef;

    if (userId) {
      userRef = admin.firestore().collection("users").doc(userId);
      userDoc = await userRef.get();
    } else if (email) {
      // Find user by email
      const usersQuery = await admin.firestore().collection("users").where("email", "==", email).get();
      if (usersQuery.empty) {
        res.status(404).json({ error: 'User not found with this email' });
        return;
      }
      userDoc = usersQuery.docs[0];
      userRef = userDoc.ref;
    }

    if (!userDoc || !userDoc.exists || !userRef) {
      res.status(404).json({ error: 'User document not found' });
      return;
    }

    // Update the user document
    await userRef.update({
      emailVerified: true,
      isVerified: true,
      verifiedAt: admin.firestore.FieldValue.serverTimestamp(),
      lastUpdated: admin.firestore.FieldValue.serverTimestamp(),
      verificationMethod: 'manual_cloud_function'
    });

    console.log(`Successfully updated isVerified to true for user ${userDoc.id}`);
    
    res.status(200).json({
      success: true,
      message: 'Email verification status updated successfully',
      userId: userDoc.id
    });

  } catch (error: any) {
    console.error('Error updating email verification:', error);
    res.status(500).json({
      error: 'Internal server error',
      message: error.message
    });
  }
});

export const updateFirestoreOnEmailVerification = functions.firestore
  .document("users/{userId}")
  .onUpdate(async (change, context) => {
    const oldUserData = change.before.data();
    const newUserData = change.after.data();
    const userId = context.params.userId;

    if (!newUserData) {
      console.log(
        "No new user data found in Firestore document event. " +
        "Skipping."
      );
      return null;
    }

    const oldEmailVerifiedInFirestore = oldUserData?.emailVerified || false;
    const newEmailVerifiedInFirestore = newUserData.emailVerified || false;

    if (
      newEmailVerifiedInFirestore === true &&
      oldEmailVerifiedInFirestore === false
    ) {
      console.log(
        `Email verification status for user ${userId} changed to ` +
        "true in Firestore."
      );

      if (newUserData.isVerified !== true) {
        const userRef = admin.firestore().collection("users").doc(userId);
        try {
          await userRef.update({
            isVerified: true,
          });
          console.log(
            `Firestore document for user ${userId} updated: ` +
            "'isVerified' set to true."
          );
        } catch (error) {
          console.error(
            `Error updating Firestore for user ${userId}:`,
            error
          );
        }
      } else {
        console.log(
          `Firestore document for user ${userId} already marked as ` +
          "verified. Skipping update."
        );
      }
    } else {
      console.log(
        `Email verification status for user ${userId} did not change ` +
        "to verified or was already verified. Skipping."
      );
    }
    return null;
  });
