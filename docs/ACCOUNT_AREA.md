# Account Area Documentation

## Overview

The account area provides authenticated users with access to their orders, downloads, and profile management. All account pages require authentication and are protected from search engine indexing.

## Dashboard (`/account`)

**Features:**
- Order history summary
- Available downloads overview
- Quick access to recent purchases
- Profile management links

**Access Control:**
- Requires authentication
- `noindex` meta tag applied
- Blocked in robots.txt

## Orders (`/account/orders`)

### Order Management
- **Order History**: Complete list of user's orders
- **Order Details**: Individual order view with items and status
- **Download Grants**: Access to purchased files per order

### Order Status Flow
1. `pending` - Order created, payment processing
2. `completed` - Payment successful, downloads available
3. `failed` - Payment failed or cancelled

### Order Structure
```php
Order {
    id, user_id, total, currency, status,
    stripe_session_id, created_at, updated_at
}

OrderItem {
    id, order_id, product_id, quantity, price,
    created_at, updated_at
}
```

## Downloads (`/downloads`)

### Download System
- **My Downloads**: List of all available download grants
- **Download Tokens**: Temporary tokens for secure file access
- **Download Statistics**: Usage tracking and limits

### Download Grant Model
```php
DownloadGrant {
    id, user_id, downloadable_file_id, order_id,
    max_downloads, download_count, expires_at,
    created_at, updated_at
}
```

### Download Process
1. User clicks download link
2. System generates temporary token (5-minute expiry)
3. Token validates grant and file access
4. File served with proper headers
5. Download count incremented

### File Security
- Files stored outside public directory
- Access via controller with authentication
- Temporary tokens prevent direct linking
- Download limits enforced per grant

## Rate Limiting

### Download Token Generation
- **Limit**: 5 requests per minute per user
- **Scope**: Per authenticated user
- **Purpose**: Prevent token abuse and system overload

### Implementation
```php
// In DownloadController@issueToken
RateLimiter::for('download-tokens', function (Request $request) {
    return Limit::perMinute(5)->by($request->user()->id);
});
```

### Rate Limit Headers
- `X-RateLimit-Limit`: Maximum requests allowed
- `X-RateLimit-Remaining`: Requests remaining in window
- `Retry-After`: Seconds until rate limit resets (when exceeded)

## Ownership Verification

### Download Grant Ownership
- Users can only access their own download grants
- Grants are tied to specific orders and users
- Cross-user access is prevented at controller level

### Security Checks
1. **Authentication**: User must be logged in
2. **Ownership**: Grant must belong to authenticated user
3. **Validity**: Grant must not be expired
4. **Limits**: Download count must not exceed maximum

### Grant Validation
```php
// Check grant belongs to user and is valid
$grant = DownloadGrant::where('user_id', auth()->id())
    ->where('id', $grantId)
    ->where('expires_at', '>', now())
    ->where('download_count', '<', 'max_downloads')
    ->firstOrFail();
```

## Download Statistics

### User Statistics
- **Total Grants**: Number of download grants owned
- **Active Grants**: Non-expired grants with remaining downloads
- **Total Downloads**: Sum of all download counts
- **Expired Grants**: Grants past expiration date

### Grant Status Tracking
- **Remaining Downloads**: `max_downloads - download_count`
- **Expiry Status**: Compared against current timestamp
- **Usage Percentage**: Download progress indicator

## API Endpoints

### Account Routes
- `GET /account` - Dashboard
- `GET /account/orders` - Order history
- `GET /account/orders/{order}` - Order details

### Download Routes
- `GET /downloads` - My downloads page
- `POST /downloads/{grant}/token` - Generate download token
- `GET /download/{token}` - Consume token and serve file

## Error Handling

### Common Scenarios
- **Expired Grant**: 404 with user-friendly message
- **Exceeded Downloads**: 403 with limit information
- **Invalid Token**: 404 or 403 depending on issue
- **Rate Limit**: 429 with retry information

### User Experience
- Clear error messages for download issues
- Helpful guidance for expired or exhausted grants
- Contact information for support requests

## Security Considerations

### File Protection
- Files stored in `storage/app/private/`
- No direct web access to file storage
- Controller-mediated access only

### Token Security
- Tokens expire after 5 minutes
- Single-use tokens (marked as used)
- Cryptographically secure random generation

### Access Control
- All account pages require authentication
- Cross-user access prevention
- Rate limiting on sensitive operations