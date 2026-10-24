## Description & Vulnerability Summary

### 1. Security Classification & CWE
- **CWE Identification**: <!-- e.g. CWE-416: Use-After-Free, CWE-125: Out-of-bounds Read, CWE-476: NULL Pointer Dereference -->
- **Policy Compliance**: <!-- Confirm this satisfies SECURITY.md and is not an un-sandboxed code execution artifact -->

### 2. Root Cause Analysis
<!-- Detailed technical explanation of why the vulnerability occurs at the C internals level -->

### 3. Proof of Vulnerability (Red-Team Verification)
- **Regression Test Added**: `path/to/test.phpt`
- **Behavior BEFORE Patch**: <!-- e.g., Triggers SEGV / ASAN heap-use-after-free report on commit <hash> -->
- **Behavior AFTER Patch**: <!-- e.g., Catches Error exception, execution completes cleanly with 0 ASAN violations -->

### 4. Technical Solution & Remediation
<!-- Describe the patch and architectural invariants enforced -->

### 5. Multi-Version Scope & Backport Audit
- [ ] `digitsflow-8.1`: <!-- Affected / Fixed / Not Applicable -->
- [ ] `digitsflow-8.2`: <!-- Affected / Fixed / Not Applicable -->
- [ ] `digitsflow-8.3`: <!-- Affected / Fixed / Not Applicable -->
- [ ] `digitsflow-8.4`: <!-- Affected / Fixed / Not Applicable -->
- [ ] `master` (8.7-dev): <!-- Affected / Fixed / Not Applicable -->

---

## Verification Quality Checklist
- [ ] Dedicated `.phpt` test included.
- [ ] Deterministic failure proven on unpatched code.
- [ ] Clean pass with AddressSanitizer (`-fsanitize=address,undefined`).
- [ ] Zero memory leaks on exception/error paths.
- [ ] No regression introduced to existing test suites.
