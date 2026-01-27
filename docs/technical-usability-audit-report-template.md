# Technical and Usability Audit Report

## Document Control
- Report Title: Technical and Usability Audit Report
- Client/Project: ICT4Farmers (App, IVR, Web Portal)
- Version: 1.0
- Prepared By: Codex (with repo review)
- Reviewers: TBD
- Date: 2026-01-24
- Distribution: Internal stakeholders (Product, Engineering, Ops)

## Executive Summary
- Overview (2-4 sentences): This audit reviews the mobile/API backend, IVR call center flow, and the web portal for security, performance, and usability risks. The application is functional but exposes critical security weaknesses in public routes and API authorization. IVR reliability and traceability improved with recent updates, yet request validation and fallback handling remain incomplete. Overall, the platform needs targeted security hardening and UX refinements to reduce user friction.
- Key Risks: Unauthenticated API access to sensitive data/actions; public web routes that run maintenance tasks; weak password handling; unverified IVR callbacks.
- Top 3 Recommendations: Protect/disable admin maintenance routes; enforce auth/authorization on APIs; remove weak password reset logic and implement rate limiting.
- Overall Posture: High risk

## Scope
- In Scope: API controllers, IVR call center controller, admin call logs/agents, web routes, key user flows.
- Out of Scope: Infrastructure, hosting/network configuration, third-party vendor dashboards, mobile client source code.
- Environments Reviewed: Local workspace + live configuration patterns inferred from routes.
- Platforms: Mobile app API, IVR (call center), web portal + admin.
- Time Period of Review: 2026-01-24

## Methodology
- Review Types: Code review, data-flow review, UX walkthrough (based on server-side flows).
- Sources Reviewed: `routes/web.php`, `routes/api.php`, `app/Http/Controllers/ApiUsersController.php`, `app/Http/Controllers/ApiShopController.php`, `app/Http/Controllers/ApiProductsController.php`, `app/Http/Controllers/CallCenter/NewCallCenterController.php`, admin controllers.
- Limitations/Assumptions: No production traffic metrics or infra access; findings are based on repository state and known runtime behavior.

## System Overview
- Architecture Summary: Laravel backend serving mobile API, web portal, and IVR callbacks. Admin panel (laravel-admin) provides management features. IVR uses Africa's Talking Voice callbacks to route calls and capture recordings.
- Key Components: Mobile API controllers, web portal routes, IVR controller, admin panel (agents + call logs), database models.
- Data Flows (high level): Mobile app calls API endpoints to manage users/products/chats; web portal handles public and dashboard routes; IVR receives callback payloads, routes to agents, records calls, stores logs/recordings.
- External Integrations: Africa's Talking (voice + SMS), storage (local filesystem), email.

## Findings Summary
- Total Findings: 14
- Critical: 3
- High: 5
- Medium: 4
- Low: 2
- Informational: 0

## Risk Matrix
| Likelihood \\ Impact | Low | Medium | High |
| --- | --- | --- | --- |
| Low | | | |
| Medium | | | |
| High | | | |

## Technical Findings
Use one block per finding.

### Finding T-01: Public maintenance routes allow destructive actions
- Severity: Critical
- Category: Security
- Affected Area: `routes/web.php`
- Evidence: `/migrate` runs migrations; `/configs-setup` runs `optimize:clear` and `storage:link` without auth.
- Risk: Unauthorized users can run migrations or alter app state.
- Recommendation: Remove from production or protect behind admin auth/IP allowlist.
- Effort: S
- Owner: Backend

### Finding T-02: Unauthenticated APIs expose sensitive data and actions
- Severity: Critical
- Category: Security
- Affected Area: `routes/api.php`, API controllers
- Evidence: Many endpoints do not enforce auth (users, products, chats, uploads).
- Risk: Data leakage, account takeover, unauthorized data modification.
- Recommendation: Require token auth, validate roles, and scope data access.
- Effort: L
- Owner: Backend + Mobile

### Finding T-03: Weak password handling and unsafe login response
- Severity: Critical
- Category: Security
- Affected Area: `app/Http/Controllers/ApiUsersController.php`
- Evidence: On wrong password, resets to `4321` and informs user to try 4321.
- Risk: Account compromise and brute-force success.
- Recommendation: Remove reset behavior, add rate limits and proper lockouts.
- Effort: S
- Owner: Backend

### Finding T-04: IVR callbacks not validated
- Severity: High
- Category: Security
- Affected Area: `app/Http/Controllers/CallCenter/NewCallCenterController.php`
- Evidence: No signature or IP validation for callbacks.
- Risk: Forged IVR payloads or data pollution.
- Recommendation: Validate Africa's Talking signature or IP allowlist.
- Effort: M
- Owner: Backend

### Finding T-05: PII overexposure in API responses
- Severity: High
- Category: Security
- Affected Area: `app/Http/Controllers/ApiUsersController.php`, `app/Http/Controllers/ApiShopController.php`
- Evidence: User/admin objects returned without field filtering.
- Risk: Exposure of sensitive fields (emails, phone numbers, internal IDs).
- Recommendation: Use transformers/DTOs; return minimal fields per endpoint.
- Effort: M
- Owner: Backend

### Finding T-06: Unbounded queries and large per_page defaults
- Severity: High
- Category: Performance
- Affected Area: `ApiUsersController::index()`, chat endpoints
- Evidence: Defaults to `per_page=1000`, unpaginated chat/messages.
- Risk: Slow queries, memory spikes, degraded API performance.
- Recommendation: Enforce pagination and reasonable limits.
- Effort: M
- Owner: Backend

### Finding T-07: Inconsistent input validation on API endpoints
- Severity: Medium
- Category: Integrity
- Affected Area: API controllers (create/update flows)
- Evidence: Manual checks, missing validation, direct use of `$_POST`.
- Risk: Data quality issues, unexpected runtime errors.
- Recommendation: Use FormRequest validation consistently.
- Effort: M
- Owner: Backend

### Finding T-08: Potential IDOR in user updates
- Severity: Medium
- Category: Security
- Affected Area: `ApiUsersController::users_account_update`
- Evidence: Accepts `user_id` from request without auth/ownership verification.
- Risk: Users may update other accounts.
- Recommendation: Require auth and verify ownership.
- Effort: M
- Owner: Backend

### Finding T-09: IVR recording download happens inline
- Severity: Medium
- Category: Performance
- Affected Area: `NewCallCenterController::downloadRecording`
- Evidence: Recording download runs during callback with network request.
- Risk: Slow IVR response, timeouts under load.
- Recommendation: Queue downloads (job/queue worker).
- Effort: M
- Owner: Backend

### Finding T-10: Improper phone uniqueness check
- Severity: Low
- Category: Integrity
- Affected Area: `ApiUsersController::users_account_update`
- Evidence: `User::find($phone)` used to check phone uniqueness.
- Risk: False positives/negatives on phone conflicts.
- Recommendation: Use `User::where('phone_number', $phone)`.
- Effort: S
- Owner: Backend

### Finding T-11: API index endpoint allows arbitrary model access
- Severity: High
- Category: Security
- Affected Area: `ApiShopController::index()`
- Evidence: `api/{model}` constructs class name from request.
- Risk: Unauthorized access to models, data leakage.
- Recommendation: Whitelist allowed models and enforce auth.
- Effort: M
- Owner: Backend

## Usability Findings
Use one block per finding.

### Finding U-01: Login flow encourages insecure password behavior
- Severity: High
- User Impact: Confusing and insecure login retries; undermines trust.
- Affected Flow: Mobile/API login
- Evidence: Wrong password response instructs user to try 4321.
- Recommendation: Replace with standard error + password reset flow.
- Effort: S
- Owner: Backend + Product

### Finding U-02: IVR error handling lacks clear retry guidance
- Severity: Medium
- User Impact: Users may repeat wrong input without clarity.
- Affected Flow: IVR language/category menus
- Evidence: Single "wrong selection" response without clear retry loop.
- Recommendation: Add explicit retry prompts and loop count.
- Effort: S
- Owner: IVR/Backend

### Finding U-03: API error messages inconsistent and technical
- Severity: Medium
- User Impact: Inconsistent messages create confusion for end users.
- Affected Flow: Mobile/API error handling
- Evidence: Mixed phrases like "update app" vs "not found".
- Recommendation: Standardize response messages and codes.
- Effort: M
- Owner: Backend + Product

## Performance Gaps
Use one block per issue.

### Gap P-01: Chat messages and heads unpaginated
- Severity: High
- Metric/Observation: Returns all messages/heads for a user.
- Root Cause: No pagination or limit in queries.
- Recommendation: Paginate, add limit/offset, and index columns.
- Effort: M

### Gap P-02: Large payload responses for users and products
- Severity: Medium
- Metric/Observation: API returns full objects with many fields.
- Root Cause: No transformers or field selection.
- Recommendation: Return minimal fields, add pagination.
- Effort: M

## IVR (Call Center) Review
### Previous State (baseline)
- Routing logic: Hard-coded agent numbers per language/category.
- Data captured: Partial; recording and duration only when certain fields present.
- Agent management: Not linked to admin-managed agents.
- Recording handling: Relied on external recording URL only.
- User experience: Wrong selection handling inconsistent; limited observability.

### Current State (post-update)
- Routing logic: Agents sourced from UNFFE Agents, language/category matching, SIP fallback.
- Data captured: Full callback payload stored; dial metadata captured.
- Agent management: Managed via admin UI with language/category/priority.
- Recording handling: Local recording download + playback in admin.
- User experience: "No agents available" response added; improved traceability.

### Remaining Gaps
- Gap: Callback authentication/validation missing.
- Impact: Forged IVR data or malicious requests.
- Recommendation: Implement signature verification or IP allowlist.

## Web Portal Review
- Navigation and discoverability: Mixed public and dashboard routes; not clearly separated.
- Key friction points: Maintenance routes accessible publicly.
- Forms and validation: Inconsistent validation on some forms.
- Accessibility concerns: Not assessed from UI code in this review.
- Recommendations: Harden public routes; add consistent validation and error messages.

## Mobile/API Review
- Authentication and authorization: Many endpoints are unauthenticated.
- Input validation: Heavy use of manual `$_POST` checks; inconsistent.
- Error handling: Mixed response structures; sometimes insecure guidance.
- Data exposure: Full objects returned without field selection.
- Recommendations: Enforce auth, add validation, use DTOs, standardize responses.

## Remediation Roadmap
| Priority | Item | Effort | Owner | Target Date |
| --- | --- | --- | --- | --- |
| P0 | Remove/protect /migrate and /configs-setup routes | S | Backend | ASAP |
| P0 | Enforce auth on critical API endpoints | L | Backend + Mobile | ASAP |
| P0 | Remove password reset to 4321; add rate limits | S | Backend | ASAP |
| P1 | Implement IVR callback verification | M | Backend | 2-4 weeks |
| P1 | Paginate users/chats/products | M | Backend | 2-4 weeks |
| P2 | Standardize API responses + DTOs | M | Backend | 4-8 weeks |

## Testing and Verification Plan
- Tests to add: API auth/authorization tests, IVR callback validation tests.
- Monitoring to add: IVR error rates, API 4xx/5xx metrics, recording download failures.
- Success criteria: No unauthenticated access to protected endpoints; stable IVR logging and recording availability; p95 API latency within target.

## Appendix
- Code References: `routes/web.php`, `routes/api.php`, `app/Http/Controllers/ApiUsersController.php`, `app/Http/Controllers/ApiShopController.php`, `app/Http/Controllers/ApiProductsController.php`, `app/Http/Controllers/CallCenter/NewCallCenterController.php`
- Screenshots/Mockups: N/A
- Logs/Artifacts: IVR callback logs from Africa's Talking
