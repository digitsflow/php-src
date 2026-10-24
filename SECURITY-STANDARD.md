# Security Vulnerability & Bugfix Quality Standard

This repository adheres to the **5-Stage Vulnerability Qualification & Verification Pipeline (VQP)** to ensure all reported and addressed security vulnerabilities are **100% verified, validated against PHP security policy, backed by deterministic test cases, and confirmed clean via dynamic memory sanitizers (ASAN/UBSAN)**.

---

## 1. Security Classification & Policy

Before any issue is treated as a security vulnerability, it must satisfy the trust boundary definitions in `./SECURITY.md` and the official [PHP Security Classification Policy](https://raw.githubusercontent.com/php/policies/refs/heads/main/security-classification.rst):

### Valid Security Vulnerabilities
- **Memory Safety Violations**: Heap/stack buffer overflow, use-after-free (UAF), double-free, out-of-bounds over-read/write occurring during normal execution or when handling untrusted external inputs.
- **Re-entrancy Hazards**: Internal C state corruption caused when userland PHP callbacks (e.g. `CURLOPT_WRITEFUNCTION`, SAX XML handlers) mutate or free resources actively executing on the stack.
- **Denial of Service / Uncontrolled Crashes**: Null-pointer dereferences or crashes reachable through safe, untrusted inputs (e.g., malformed ZIP, XML, JSON, or images) that bypass expected exception handling.

### Excluded (Standard Bugs, Not Vulnerabilities)
- Invoking specially crafted code (e.g. custom error handlers, destructors, or `__toString()`) designed to violate memory during known unsafe operations. PHP is not a sandbox.
- Memory exhaustion where `memory_limit` cleanly aborts the request.
- Bypassing `open_basedir` or `disable_functions` via standard runtime behavior.

---

## 2. The 5-Stage Verification Standard

```
[1. Triage & CWE] ➔ [2. Red Test (Proof)] ➔ [3. Patch Engineering] ➔ [4. Sanitizer Gate] ➔ [5. Regression Protection]
```

### Stage 1: Triage & Classification
- Identify the exact **CWE** (Common Weakness Enumeration), e.g.:
  - `CWE-416`: Use-After-Free
  - `CWE-125`: Out-of-bounds Read
  - `CWE-787`: Out-of-bounds Write
  - `CWE-476`: NULL Pointer Dereference

### Stage 2: Red-Team Test Creation (Proof of Vulnerability)
- Every vulnerability fix MUST be accompanied by an automated `.phpt` test.
- **Proof of Vulnerability**: When the test is run against the unpatched binary, it MUST deterministically:
  - Crash (Segmentation Fault, abort), OR
  - Trigger an AddressSanitizer/UBSan violation report, OR
  - Fail the `--EXPECT--` condition.
- If the test does not fail on the unpatched code, the vulnerability is unverified.

### Stage 3: Defensive Patch Engineering
- Avoid ad-hoc workarounds; fix the root architectural invariant.
- Re-entrancy guards: inspect flags (e.g., `in_callback`, `isparsing`, `in_use`) and throw clean `Error` exceptions before executing state changes.
- Check pointer validity and allocations prior to dereference or passing to `strlen()`.

### Stage 4: Dynamic Sanitizer Verification (ASAN / UBSAN)
- Build the binary with AddressSanitizer and UndefinedBehaviorSanitizer:
  ```bash
  CFLAGS="-fsanitize=address,undefined -fno-omit-frame-pointer -g -O1" \
  LDFLAGS="-fsanitize=address,undefined" \
  ./configure --enable-debug ...
  ```
- Run the `.phpt` test suite with `ASAN_OPTIONS="detect_leaks=1:abort_on_error=1"`.
- The test MUST pass with:
  - **Zero segmentation faults**
  - **Zero AddressSanitizer heap/stack violation reports**
  - **Zero UndefinedBehaviorSanitizer warnings**
  - **Zero memory leaks on error/exception throw paths**

### Stage 5: Version Scope & Dual Verification
- Audit whether the issue affects other active branches (`digitsflow-8.1`, `digitsflow-8.2`, `digitsflow-8.3`, `digitsflow-8.4`, and `master`).
- Backport the fix and `.phpt` test across all vulnerable branches.
