# KYXTRO SMM Panel — Project Specification

## 1. Project Overview

**Name:** KYXTRO SMM Panel
**Type:** Social Media Marketing (SMM) Reseller Platform
**Tech Stack:** PHP 8.2+, Laravel 11, MySQL 8.0, Bootstrap 5, Alpine.js
**Target:** Entrepreneurs who want to resell social media services (followers, likes, views, etc.)
**Inspiration:** Perfect Panel (perfectpanel.com)

---

## 2. Functionality Overview

### 2.1 User Roles
- **Guest** — Browse public storefront, register
- **User** — Buy services, place orders, manage deposits, view history, submit tickets
- **Admin** — Full system control: users, services, providers, orders, payments, reports, settings

### 2.2 Core Features

#### Public Storefront
- Landing page with hero, features, how-it-works, testimonials, FAQ
- Service catalog with categories and individual service cards
- Service details: name, price per 1K, min/max order, description, reviews
- SEO meta tags, sitemap, robots.txt
- Multi-language support (English default, extensible)
- RTL support

#### User Dashboard
- Order placement (single, mass/bulk, subscription/auto)
- Drip-feed ordering (time intervals + quantity)
- Order history with status tracking
- Deposit funds (payment methods)
- API keys management (for resellers)
- Support tickets
- Profile settings

#### Admin Dashboard
- **Dashboard Home** — Stats: total users, orders today, revenue, pending tickets
- **User Management** — CRUD users, custom rates, suspend/activate, export CSV
- **Service Management** — CRUD services, categories, bulk rate editing, copy from providers
- **Provider Management** — Add providers, import/sync services, balance check
- **Order Management** — All orders table, status management, refund, partial quantity, export CSV
- **Payment Management** — Payment methods, deposits, bonuses, export
- **Ticket System** — View/respond to tickets, saved replies
- **Reports** — Profit report, order report, payment report, activity log
- **Settings** — Site settings, theme, SEO, notifications

---

## 3. Database Schema

### Tables

#### `users`
| Column | Type | Description |
|---|---|---|
| id | bigint PK | |
| name | varchar | |
| email | varchar unique | |
| password | varchar | |
| role | enum('user','admin') | |
| balance | decimal(15,4) | User's deposit balance |
| is_active | bool | Suspended/active |
| email_verified_at | timestamp | |
| remember_token | varchar | |
| created_at | timestamp | |
| updated_at | timestamp | |

#### `user_profiles`
| Column | Type | Description |
|---|---|---|
| id | bigint PK | |
| user_id | bigint FK | |
| api_key | varchar unique | Reseller API key |
| timezone | varchar | |
| language | varchar | |
| created_at | timestamp | |
| updated_at | timestamp | |

#### `categories`
| Column | Type | Description |
|---|---|---|
| id | bigint PK | |
| name | varchar | |
| slug | varchar | |
| icon | varchar | FontAwesome class |
| sort_order | int | |
| is_active | bool | |
| created_at | timestamp | |
| updated_at | timestamp | |

#### `services`
| Column | Type | Description |
|---|---|---|
| id | bigint PK | |
| category_id | bigint FK | |
| name | varchar | |
| description | text | |
| provider_service_id | varchar | ID from provider API |
| provider_id | bigint FK | |
| price_per_1k | decimal(10,4) | Default rate |
| cost_per_1k | decimal(10,4) | Provider cost |
| min_order | int | |
| max_order | int | |
| dripfeed | bool | Supports drip-feed |
| refill | bool | Supports refill |
| cancel | bool | Can be cancelled |
| average_time | varchar | e.g. "24 hours" |
| description_extra | text | Extra notes |
| is_active | bool | |
| is_featured | bool | |
| created_at | timestamp | |
| updated_at | timestamp | |

#### `user_custom_rates`
| Column | Type | Description |
|---|---|---|
| id | bigint PK | |
| user_id | bigint FK | |
| service_id | bigint FK | |
| custom_rate | decimal(10,4) | Custom price override |
| created_at | timestamp | |
| updated_at | timestamp | |

#### `providers`
| Column | Type | Description |
|---|---|---|
| id | bigint PK | |
| name | varchar | |
| api_url | varchar | |
| api_key | varchar | |
| is_active | bool | |
| balance | decimal(15,4) | |
| last_sync_at | timestamp | |
| created_at | timestamp | |
| updated_at | timestamp | |

#### `orders`
| Column | Type | Description |
|---|---|---|
| id | bigint PK | |
| order_id | varchar unique | Public order ID |
| user_id | bigint FK | |
| service_id | bigint FK | |
| link | varchar | Target URL |
| quantity | int | |
| charge | decimal(15,4) | User charged |
| cost | decimal(15,4) | Provider cost |
| profit | decimal(15,4) | charge - cost |
| status | enum | pending/processing/completed/partial/cancelled/refunded |
| start_count | int | Provider start count |
| remains | int | |
| drip_id | int nullable | Reference for drip-feed orders |
| api_response | text | Raw provider response |
| created_at | timestamp | |
| updated_at | timestamp | |

#### `dripfeeds`
| Column | Type | Description |
|---|---|---|
| id | bigint PK | |
| user_id | bigint FK | |
| order_id | varchar | Related main order |
| service_id | bigint FK | |
| link | varchar | |
| runs | int | Number of runs |
| interval | int | Minutes between runs |
| quantity | int | Per run |
| total_quantity | int | |
| total_charged | decimal(15,4) | |
| status | enum | active/paused/completed |
| created_at | timestamp | |
| updated_at | timestamp | |

#### `subscriptions`
| Column | Type | Description |
|---|---|---|
| id | bigint PK | |
| sub_id | varchar unique | Public subscription ID |
| user_id | bigint FK | |
| service_id | bigint FK | |
| link | varchar | |
| posts | int | Number of posts |
| quantity | int | Per post |
| delay | int | Minutes delay |
| expiry | datetime nullable | |
| total_charged | decimal(15,4) | |
| status | enum | active/paused/completed/cancelled |
| last_order_at | timestamp | |
| created_at | timestamp | |
| updated_at | timestamp | |

#### `payments`
| Column | Type | Description |
|---|---|---|
| id | bigint PK | |
| user_id | bigint FK | |
| method | varchar | Payment method name |
| amount | decimal(15,4) | Amount in USD |
| amount_bonus | decimal(15,4) | Bonus added |
| net_amount | decimal(15,4) | amount + bonus |
| transaction_id | varchar | External tx ID |
| status | enum | pending/completed/failed |
| note | text | Admin note |
| created_at | timestamp | |
| updated_at | timestamp | |

#### `payment_methods`
| Column | Type | Description |
|---|---|---|
| id | bigint PK | |
| name | varchar | |
| slug | varchar | e.g. "paypal" |
| is_active | bool | |
| min_amount | decimal(10,2) | |
| max_amount | decimal(10,2) | |
| fixed_charge | decimal(10,2) | Fixed fee |
| percent_charge | decimal(5,2) | % fee |
| bonus_percent | decimal(5,2) | Deposit bonus % |
| instructions | text | How to pay |
| credentials | text | Encrypted JSON (API keys, etc.) |
| sort_order | int | |
| created_at | timestamp | |
| updated_at | timestamp | |

#### `refills`
| Column | Type | Description |
|---|---|---|
| id | bigint PK | |
| refill_id | varchar unique | |
| order_id | bigint FK | |
| user_id | bigint FK | |
| quantity_requested | int | |
| status | enum | pending/completed/failed |
| created_at | timestamp | |
| updated_at | timestamp | |

#### `tickets`
| Column | Type | Description |
|---|---|---|
| id | bigint PK | |
| ticket_id | varchar unique | |
| user_id | bigint FK | |
| subject | varchar | |
| priority | enum | low/medium/high |
| status | enum | open/answered/closed |
| last_reply_at | timestamp | |
| created_at | timestamp | |
| updated_at | timestamp | |

#### `ticket_messages`
| Column | Type | Description |
|---|---|---|
| id | bigint PK | |
| ticket_id | bigint FK | |
| user_id | bigint FK nullable | Admin if null |
| message | text | |
| created_at | timestamp | |

#### `ticket_replies` (saved admin replies)
| Column | Type | Description |
|---|---|---|
| id | bigint PK | |
| title | varchar | |
| message | text | |
| created_at | timestamp | |
| updated_at | timestamp | |

#### `activity_logs`
| Column | Type | Description |
|---|---|---|
| id | bigint PK | |
| user_id | bigint FK nullable | |
| action | varchar | e.g. "order_created" |
| description | varchar | |
| ip_address | varchar | |
| user_agent | text | |
| created_at | timestamp | |

#### `settings`
| Column | Type | Description |
|---|---|---|
| id | bigint PK | |
| key | varchar unique | Setting key |
| value | text | JSON-encoded if needed |
| type | varchar | string/int/bool/json |
| created_at | timestamp | |
| updated_at | timestamp | |

---

## 4. API — Provider Integration

### Standard SMM Provider API Format
Providers like `smmapi.io`, `peakerr`, `socialpanel` use a common API pattern:

```
POST https://provider.com/api/v2
{
  "key": "API_KEY",
  "action": "services" | "add" | "status" | "refill" | "balance"
}
```

### Actions
| Action | Request | Response |
|---|---|---|
| List services | `action: services` | Array of { id, name, rate, min, max, ... } |
| Place order | `action: add` | { order: "123" } |
| Order status | `action: status`, `order: 123` | { status, starts, remains } |
| Refill order | `action: refill`, `order: 123` | { refill: "456" } |
| Get balance | `action: balance` | { balance: "100.00" } |

---

## 5. Order Processing Flow

```
1. User places order
   └── Validate: min/max, balance, service active
   └── Calculate charge: quantity × price_per_1k / 1000
   └── Deduct user balance
   └── Create order record (status: pending)

2. Dispatch to Provider API
   └── POST provider API with service_id + link + quantity
   └── Save provider order ID + response
   └── Update status to processing

3. Status Sync (scheduled job / webhook)
   └── Poll provider API for status
   └── Update order: start_count, remains, status
   └── If completed: mark done
   └── If partial: mark partial, credit back remainder

4. Drip-feed (scheduled job)
   └── Every X minutes, send partial quantity order
   └── Continue until all runs complete

5. Subscriptions (scheduled job)
   └── On new post to monitored link, auto-order
   └── Continue until expiry or cancellation
```

---

## 6. Acceptance Criteria

- [ ] Admin can create/edit/delete categories and services
- [ ] Admin can add providers and sync services from them
- [ ] Admin can manage all orders (view, refund, partial complete)
- [ ] Admin can manage users (custom rates, suspend, export)
- [ ] Users can register, login, reset password
- [ ] Users can browse services, place single/bulk/subscription orders
- [ ] Users can deposit funds via at least 3 payment methods
- [ ] Orders are dispatched to providers and status is tracked
- [ ] Drip-feed orders process on schedule
- [ ] Users can open/respond to support tickets
- [ ] Public storefront with responsive design
- [ ] Admin dashboard with statistics and reports
- [ ] Activity logging for audit trail
- [ ] API key system for reseller access
