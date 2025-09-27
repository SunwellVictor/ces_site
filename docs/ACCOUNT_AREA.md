# Account Area Documentation

## Overview
The account area provides authenticated users with access to their orders, downloads, and profile management. All account routes require authentication and email verification.

## Pages & Routes

### Account Dashboard
- **Route**: `/account` (`account.dashboard`)
- **Controller**: `AccountController@dashboard`
- **Purpose**: Main account overview with stats and recent activity
- **Data Displayed**:
  - User profile information
  - Recent orders (last 5)
  - Active download grants
  - Account statistics (total orders, completed orders, total spent, available downloads)

### Order Management
- **Orders List**: `/account/orders` (`account.orders`)
  - Paginated order history (10 per page)
  - Filters: status, date range
  - Order statistics breakdown
- **Order Details**: `/account/orders/{order}` (`account.orders.show`)
  - Full order details with items and download grants
  - Ownership verification required

### Download Management
- **Downloads List**: `/account/downloads` (`account.downloads`)
  - Active download grants with pagination
  - Grouped by product for organization
  - Shows remaining downloads and expiry dates
- **Download Stats**: `/downloads/stats` (API endpoint)
  - Total grants, active grants, total downloads, expired grants

### Profile Management
- **Profile Edit**: `/account/profile/edit` (`account.profile.edit`)
- **Profile Update**: `/account/profile` (`account.profile.update`)
  - Name and email validation
  - Unique email constraint

## Authentication & Authorization

### Middleware Stack
1. **Authentication Required**: All account routes use `auth` middleware
2. **Email Verification**: Account routes require `verified` middleware
3. **Guest Redirect**: Unauthenticated users redirected to login

### Ownership Checks
- **Order Access**: `$order->user_id !== Auth::id()` returns 404
- **Download Grant Access**: `$grant->user_id !== Auth::id()` returns 403
- **Download Token Access**: Verified through grant ownership chain

## Token Issuance & Rate Limiting

### Download Token System
- **Purpose**: Temporary tokens for secure file downloads
- **Validity**: 10 minutes from issuance
- **Single Use**: Tokens are marked as used after consumption

### Rate Limiting Rules
- **Token Issuance**: 5 requests per minute per user
- **Rate Limit Key**: `download-token:{user_id}`
- **Response**: 429 status with retry_after seconds
- **Implementation**: Laravel's RateLimiter facade

### Token Lifecycle
1. **Issue**: POST `/downloads/{grant}/token`
   - Validates grant ownership and validity
   - Creates UUID token with 10-minute expiry
   - Returns download URL and expiration time
2. **Consume**: GET `/download/{token}`
   - Validates token and grant
   - Increments download count
   - Marks token as used
   - Streams file with proper headers

### Grant Validation
- **Expiry Check**: `expires_at` must be future or null
- **Download Limit**: `downloads_used < max_downloads`
- **File Existence**: Verifies file exists on storage disk

## Security Features

### Access Control
- Route-level authentication middleware
- Email verification requirement
- Ownership verification for all resources
- CSRF protection on state-changing operations

### Download Security
- Temporary tokens prevent direct file access
- Single-use tokens prevent replay attacks
- Rate limiting prevents abuse
- File streaming with security headers

### Data Protection
- User data scoped to authenticated user
- No cross-user data leakage
- Proper HTTP status codes for unauthorized access

## Error Handling

### Common Error Responses
- **401 Unauthorized**: Missing authentication
- **403 Forbidden**: Access denied (ownership/permissions)
- **404 Not Found**: Resource doesn't exist or no access
- **429 Too Many Requests**: Rate limit exceeded
- **410 Gone**: Download grant no longer valid

### Validation Errors
- Profile updates validate name (required, max 255)
- Email validation includes uniqueness check
- Download token requests validate grant ownership

## Configuration

### Download Limits (config/stripe.php)
```php
'download_grants' => [
    'max_downloads' => env('STRIPE_MAX_DOWNLOADS', 3),
    'expires_years' => env('STRIPE_EXPIRES_YEARS', 1),
]
```

### Rate Limiting
- Token issuance: 5 requests/minute
- Configurable through RateLimiter::hit() parameters
- Uses Redis/cache for distributed rate limiting

## Testing
Account area functionality is covered by:
- `tests/Feature/AccountTest.php` - Authentication and access control
- `tests/Feature/DownloadTest.php` - Download token system
- Route protection tests for all account endpoints