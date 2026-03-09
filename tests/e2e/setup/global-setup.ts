import { loginAndSaveSession, hasUsableStorageState } from '../helpers/auth.js';

export default async function globalSetup(): Promise<void> {
  console.log('Global setup: checking authentication state...');

  // Login as regular user if state is missing/stale
  if (!(await hasUsableStorageState('regular'))) {
    console.log('Logging in as regular user...');
    await loginAndSaveSession('regular');
    console.log('Regular user session saved.');
  } else {
    console.log('Regular user session still valid, skipping login.');
  }

  // Login as admin user if state is missing/stale
  if (!(await hasUsableStorageState('admin'))) {
    console.log('Logging in as admin user...');
    await loginAndSaveSession('admin');
    console.log('Admin user session saved.');
  } else {
    console.log('Admin user session still valid, skipping login.');
  }

  console.log('Global setup complete.');
}
