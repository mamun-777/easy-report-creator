const TOKEN_KEY = "erc_admin_token";

const state = {
  username: "",
  kind: "user",
  licenses: [],
  intents: [],
  licenseFilter: "all",
  purchaseFilter: "pending",
  licenseQuery: "",
  purchaseQuery: "",
  customerQuery: "",
};

const PAGE_META = {
  overview: ["Overview", "Monitor licenses and customer access at a glance."],
  licenses: ["Licenses", "Search, filter, and manage all issued license keys."],
  purchases: ["Purchases", "Fulfil Moneybird checkouts and email license keys."],
  customers: ["Customers", "Contact details for everyone who holds a license."],
};

async function api(action, options = {}) {
  const headers = { "Content-Type": "application/json", ...(options.headers || {}) };
  const token = localStorage.getItem(TOKEN_KEY);
  if (token) headers["X-Admin-Token"] = token;
  const res = await fetch(`api.php?action=${encodeURIComponent(action)}`, {
    method: options.method || "GET",
    headers,
    body: options.body ? JSON.stringify(options.body) : undefined,
  });
  const data = await res.json().catch(() => ({}));
  if (!res.ok || data.ok === false) {
    const err = new Error(data.detail || res.statusText || "Request failed");
    err.status = res.status;
    throw err;
  }
  return data;
}

function $(id) {
  return document.getElementById(id);
}

function toast(message, kind = "success") {
  const el = document.createElement("div");
  const cls = kind === "danger" || kind === "error" ? "error" : kind === "warn" ? "error" : "success";
  el.className = `toast show ${cls}`;
  el.textContent = message;
  $("toast-container").appendChild(el);
  setTimeout(() => el.remove(), 4200);
}

function escapeHtml(s) {
  return String(s ?? "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
}

function formatWhen(iso) {
  if (!iso) return "—";
  const d = new Date(iso);
  if (Number.isNaN(d.getTime())) return String(iso).slice(0, 16).replace("T", " ");
  return d.toLocaleString(undefined, { dateStyle: "medium", timeStyle: "short" });
}

function showLogin(message) {
  $("app-shell").classList.add("hidden");
  $("login-shell").classList.remove("hidden");
  localStorage.removeItem(TOKEN_KEY);
  const msg = $("login-message");
  if (message) {
    msg.textContent = message;
    msg.classList.remove("hidden");
  } else {
    msg.classList.add("hidden");
  }
}

function showApp(username, kind = "user") {
  state.username = username || "admin";
  state.kind = kind || "user";
  $("login-shell").classList.add("hidden");
  $("app-shell").classList.remove("hidden");
  if (state.kind === "master") {
    $("current-user-name").textContent = "Master key";
    $("current-user-role").textContent = "Root access";
  } else {
    $("current-user-name").textContent = state.username;
    $("current-user-role").textContent = "Administrator";
  }
  loadDashboard(false);
}

function switchView(viewName) {
  const meta = PAGE_META[viewName] || PAGE_META.overview;
  $("page-title").textContent = meta[0];
  $("page-subtitle").textContent = meta[1];
  document.querySelectorAll(".nav-item[data-view]").forEach((btn) => {
    btn.classList.toggle("active", btn.dataset.view === viewName);
  });
  document.querySelectorAll(".view").forEach((section) => {
    section.classList.toggle("active", section.id === `view-${viewName}`);
  });
  closeSidebar();
  if (viewName === "licenses") renderLicenses();
  if (viewName === "purchases") renderPurchases();
  if (viewName === "customers") renderCustomers();
  if (viewName === "overview") renderOverview();
}

function setSidebarOpen(open) {
  $("sidebar").classList.toggle("open", open);
  $("sidebar-backdrop").classList.toggle("hidden", !open);
}

function closeSidebar() {
  setSidebarOpen(false);
}

function openModal(title, bodyHtml, footerHtml) {
  $("modal-title").textContent = title;
  $("modal-body").innerHTML = bodyHtml;
  const footer = $("modal-footer");
  if (footerHtml) {
    footer.innerHTML = footerHtml;
    footer.classList.remove("hidden");
  } else {
    footer.innerHTML = "";
    footer.classList.add("hidden");
  }
  $("modal-backdrop").classList.remove("hidden");
}

function closeModal() {
  $("modal-backdrop").classList.add("hidden");
}

async function loadDashboard(showToast) {
  const [statsRes, licRes, intRes] = await Promise.all([
    api("stats"),
    api("licenses"),
    api("intents"),
  ]);
  state.licenses = licRes.licenses || [];
  state.intents = intRes.intents || [];
  const s = statsRes.stats || {};
  $("stats-total-licenses").textContent = s.keys_total ?? 0;
  $("stats-available-licenses").textContent = s.keys_available ?? 0;
  $("stats-licenses-in-use").textContent = s.keys_active ?? 0;
  $("stats-revoked-licenses").textContent = s.keys_revoked ?? 0;
  $("stats-pending-purchases").textContent = s.intents_pending ?? 0;
  updatePurchaseBadge(s.intents_pending ?? 0);
  renderOverview();
  renderLicenses();
  renderPurchases();
  renderCustomers();
  if (showToast) toast("Dashboard refreshed");
}

function updatePurchaseBadge(count) {
  const badge = $("nav-purchases-badge");
  if (count > 0) {
    badge.textContent = String(count);
    badge.classList.remove("hidden");
  } else {
    badge.classList.add("hidden");
  }
}

function renderOverview() {
  const pending = state.intents.filter((i) => i.status === "pending");
  const panel = $("pending-purchases-panel");
  const list = $("pending-purchases-list");
  if (!pending.length) {
    panel.classList.add("hidden");
  } else {
    panel.classList.remove("hidden");
    list.innerHTML = pending
      .slice(0, 5)
      .map(
        (row) => `<div class="notification-item">
          <div>
            <strong>${escapeHtml(row.full_name)}</strong> started checkout
            <div class="muted">${escapeHtml(row.email)} · ${escapeHtml(row.vat_region || "")}</div>
          </div>
          <button type="button" class="btn btn-ghost btn-small" data-fulfil="${escapeHtml(
            row.intent_id
          )}">Issue key</button>
        </div>`
      )
      .join("");
    list.querySelectorAll("[data-fulfil]").forEach((btn) => {
      btn.addEventListener("click", () => fulfilIntent(btn.dataset.fulfil));
    });
  }

  const activity = [];
  state.licenses.slice(0, 8).forEach((lic) => {
    activity.push({
      when: lic.created_at,
      text: `License issued to ${lic.customer_name || "Customer"}`,
      detail: lic.licence_key,
    });
  });
  state.intents.slice(0, 5).forEach((intent) => {
    activity.push({
      when: intent.created_at,
      text: `${intent.full_name} started purchase`,
      detail: intent.email,
    });
  });
  activity.sort((a, b) => String(b.when).localeCompare(String(a.when)));

  const ul = $("activity-list");
  const empty = $("activity-empty");
  if (!activity.length) {
    ul.innerHTML = "";
    empty.classList.remove("hidden");
  } else {
    empty.classList.add("hidden");
    ul.innerHTML = activity
      .slice(0, 10)
      .map(
        (item) => `<li class="activity-item">
          <div class="activity-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><circle cx="6.5" cy="12" r="3.5"/><path d="M10 12h10.5"/></svg>
          </div>
          <div>
            <div>${escapeHtml(item.text)}</div>
            <div class="muted mono">${escapeHtml(item.detail || "")}</div>
          </div>
          <div class="activity-time">${escapeHtml(formatWhen(item.when))}</div>
        </li>`
      )
      .join("");
  }
}

function statusBadge(status) {
  const map = {
    available: "badge-success",
    in_use: "badge-neutral",
    revoked: "badge-danger",
    pending: "badge-warn",
    fulfilled: "badge-success",
  };
  return `<span class="badge ${map[status] || "badge-neutral"}">${escapeHtml(
    String(status).replace("_", " ")
  )}</span>`;
}

function renderLicenses() {
  const q = state.licenseQuery.trim().toLowerCase();
  const rows = state.licenses.filter((lic) => {
    if (state.licenseFilter !== "all" && lic.status !== state.licenseFilter) return false;
    if (!q) return true;
    return (
      String(lic.licence_key).toLowerCase().includes(q) ||
      String(lic.customer_name || "").toLowerCase().includes(q) ||
      String(lic.customer_email || "").toLowerCase().includes(q)
    );
  });
  const body = $("licenses-table-body");
  const empty = $("licenses-empty");
  if (!rows.length) {
    body.innerHTML = "";
    empty.classList.remove("hidden");
    return;
  }
  empty.classList.add("hidden");
  body.innerHTML = rows
    .map((lic) => {
      const revoke =
        lic.status === "revoked"
          ? ""
          : `<button type="button" class="btn btn-ghost btn-small" data-revoke="${escapeHtml(
              lic.licence_key
            )}">Revoke</button>`;
      const copy = `<button type="button" class="btn btn-ghost btn-small" data-copy="${escapeHtml(
        lic.licence_key
      )}">Copy</button>`;
      return `<tr>
        <td class="mono">${escapeHtml(lic.licence_key)}</td>
        <td>${escapeHtml(lic.customer_name || "—")}<div class="muted">${escapeHtml(
          lic.customer_email || ""
        )}</div></td>
        <td>${escapeHtml(String(lic.validity_days))}d</td>
        <td>${statusBadge(lic.status)}</td>
        <td class="muted">${escapeHtml(formatWhen(lic.created_at))}</td>
        <td class="row-actions">${copy}${revoke}</td>
      </tr>`;
    })
    .join("");
  body.querySelectorAll("[data-copy]").forEach((btn) => {
    btn.addEventListener("click", async () => {
      await navigator.clipboard.writeText(btn.dataset.copy);
      toast("License key copied");
    });
  });
  body.querySelectorAll("[data-revoke]").forEach((btn) => {
    btn.addEventListener("click", () => revokeLicense(btn.dataset.revoke));
  });
}

function renderPurchases() {
  const q = state.purchaseQuery.trim().toLowerCase();
  const rows = state.intents.filter((row) => {
    if (state.purchaseFilter !== "all" && row.status !== state.purchaseFilter) return false;
    if (!q) return true;
    return (
      String(row.full_name || "").toLowerCase().includes(q) ||
      String(row.email || "").toLowerCase().includes(q) ||
      String(row.company_name || "").toLowerCase().includes(q)
    );
  });
  const body = $("purchases-table-body");
  const empty = $("purchases-empty");
  if (!rows.length) {
    body.innerHTML = "";
    empty.classList.remove("hidden");
    return;
  }
  empty.classList.add("hidden");
  body.innerHTML = rows
    .map((row) => {
      const action =
        row.status === "pending"
          ? `<button type="button" class="btn btn-primary btn-small" data-fulfil="${escapeHtml(
              row.intent_id
            )}">Issue key</button>`
          : `<span class="muted mono">${escapeHtml(row.notes || "done")}</span>`;
      return `<tr>
        <td class="muted">${escapeHtml(formatWhen(row.created_at))}</td>
        <td>${escapeHtml(row.full_name)}</td>
        <td>${escapeHtml(row.email)}</td>
        <td>${escapeHtml(row.company_name || "—")}</td>
        <td>${escapeHtml(row.vat_region || "—")}</td>
        <td>${statusBadge(row.status)}</td>
        <td>${action}</td>
      </tr>`;
    })
    .join("");
  body.querySelectorAll("[data-fulfil]").forEach((btn) => {
    btn.addEventListener("click", () => fulfilIntent(btn.dataset.fulfil));
  });
}

function renderCustomers() {
  const map = new Map();
  state.licenses.forEach((lic) => {
    const key = (lic.customer_email || lic.customer_name || lic.licence_key).toLowerCase();
    if (!map.has(key)) {
      map.set(key, {
        name: lic.customer_name || "Customer",
        email: lic.customer_email || "",
        company: lic.company_name || "",
        count: 0,
        last: lic.created_at,
      });
    }
    const row = map.get(key);
    row.count += 1;
    if (String(lic.created_at) > String(row.last)) row.last = lic.created_at;
    if (!row.email && lic.customer_email) row.email = lic.customer_email;
    if (!row.company && lic.company_name) row.company = lic.company_name;
  });
  const q = state.customerQuery.trim().toLowerCase();
  const rows = [...map.values()].filter((row) => {
    if (!q) return true;
    return (
      row.name.toLowerCase().includes(q) ||
      row.email.toLowerCase().includes(q) ||
      row.company.toLowerCase().includes(q)
    );
  });
  const body = $("customers-table-body");
  const empty = $("customers-empty");
  if (!rows.length) {
    body.innerHTML = "";
    empty.classList.remove("hidden");
    return;
  }
  empty.classList.add("hidden");
  body.innerHTML = rows
    .map(
      (row) => `<tr>
        <td>${escapeHtml(row.name)}</td>
        <td>${escapeHtml(row.email || "—")}</td>
        <td>${escapeHtml(row.company || "—")}</td>
        <td>${row.count}</td>
        <td class="muted">${escapeHtml(formatWhen(row.last))}</td>
      </tr>`
    )
    .join("");
}

function openCreateLicenseModal(prefill = {}) {
  openModal(
    "New license",
    `<div class="form-grid">
      <label class="field"><span class="field-label">Customer name</span><input id="m-name" value="${escapeHtml(
        prefill.customer_name || ""
      )}"></label>
      <label class="field"><span class="field-label">Email</span><input id="m-email" type="email" value="${escapeHtml(
        prefill.customer_email || ""
      )}"></label>
      <label class="field"><span class="field-label">Company</span><input id="m-company" value="${escapeHtml(
        prefill.company_name || ""
      )}"></label>
      <label class="field"><span class="field-label">Validity (days)</span><input id="m-days" type="number" min="1" value="365"></label>
      <label class="field full"><span class="field-label">Notes</span><input id="m-notes" value=""></label>
    </div>`,
    `<button type="button" class="btn btn-ghost" id="m-cancel">Cancel</button>
     <button type="button" class="btn btn-primary" id="m-save">Create license</button>`
  );
  $("m-cancel").onclick = closeModal;
  $("m-save").onclick = async () => {
    try {
      const created = await api("licenses", {
        method: "POST",
        body: {
          customer_name: $("m-name").value,
          customer_email: $("m-email").value,
          company_name: $("m-company").value,
          validity_days: Number($("m-days").value || 365),
          notes: $("m-notes").value,
        },
      });
      closeModal();
      toast(`Created ${created.license.licence_key}`);
      await loadDashboard(false);
      switchView("licenses");
    } catch (ex) {
      toast(ex.message, "danger");
    }
  };
}

async function revokeLicense(key) {
  if (!confirm(`Revoke ${key}?`)) return;
  try {
    await api("revoke", { method: "POST", body: { licence_key: key } });
    toast("License revoked", "warn");
    await loadDashboard(false);
  } catch (ex) {
    toast(ex.message, "danger");
  }
}

async function fulfilIntent(intentId) {
  try {
    const created = await api("fulfil_intent", {
      method: "POST",
      body: { intent_id: intentId },
    });
    const key = created.license.licence_key;
    const mailed = created.license.emailed ? "Email sent." : "Copy the key — email could not be sent.";
    toast(`Issued ${key}. ${mailed}`);
    await navigator.clipboard.writeText(key).catch(() => {});
    await loadDashboard(false);
  } catch (ex) {
    toast(ex.message, "danger");
  }
}

function bindUi() {
  const modeToggle = $("login-mode-toggle");
  modeToggle?.addEventListener("click", () => {
    const master = $("login-masterkey");
    const userpass = $("login-userpass");
    const toMaster = master.classList.contains("hidden");
    master.classList.toggle("hidden", !toMaster);
    userpass.classList.toggle("hidden", toMaster);
    modeToggle.textContent = toMaster
      ? "Sign in with username instead"
      : "Sign in with master API key instead";
  });

  $("toggle-api-key")?.addEventListener("click", () => {
    const input = $("admin-api-key");
    const show = input.type === "password";
    input.type = show ? "text" : "password";
    $("toggle-api-key").querySelector(".icon-show")?.classList.toggle("hidden", show);
    $("toggle-api-key").querySelector(".icon-hide")?.classList.toggle("hidden", !show);
  });

  $("login-form").addEventListener("submit", async (e) => {
    e.preventDefault();
    const msg = $("login-message");
    msg.classList.add("hidden");
    const masterMode = !$("login-masterkey").classList.contains("hidden");
    try {
      let data;
      if (masterMode) {
        const masterKey = $("admin-api-key").value.trim();
        if (!masterKey) {
          msg.textContent = "Enter the master API key.";
          msg.classList.remove("hidden");
          return;
        }
        data = await api("login", {
          method: "POST",
          body: { master_key: masterKey },
        });
      } else {
        data = await api("login", {
          method: "POST",
          body: {
            username: $("login-username").value,
            password: $("login-password").value,
          },
        });
      }
      localStorage.setItem(TOKEN_KEY, data.token);
      showApp(data.username, data.kind || "user");
    } catch (ex) {
      msg.textContent = ex.message;
      msg.classList.remove("hidden");
    }
  });

  $("logout-button").addEventListener("click", async () => {
    try {
      await api("logout", { method: "POST", body: {} });
    } catch (_) {}
    showLogin();
  });

  document.querySelectorAll(".nav-item[data-view]").forEach((btn) => {
    btn.addEventListener("click", () => switchView(btn.dataset.view));
  });
  document.querySelectorAll("[data-view-jump]").forEach((btn) => {
    btn.addEventListener("click", () => switchView(btn.dataset.viewJump));
  });
  document.querySelectorAll("[data-open-create]").forEach((btn) => {
    btn.addEventListener("click", () => openCreateLicenseModal());
  });
  $("create-license-button").addEventListener("click", () => openCreateLicenseModal());
  $("refresh-button").addEventListener("click", () => loadDashboard(true).catch((ex) => toast(ex.message, "danger")));
  $("modal-close").addEventListener("click", closeModal);
  $("modal-backdrop").addEventListener("click", (ev) => {
    if (ev.target === $("modal-backdrop")) closeModal();
  });
  $("menu-toggle")?.addEventListener("click", () => setSidebarOpen(true));
  $("sidebar-close")?.addEventListener("click", closeSidebar);
  $("sidebar-backdrop")?.addEventListener("click", closeSidebar);

  $("license-search").addEventListener("input", (e) => {
    state.licenseQuery = e.target.value;
    renderLicenses();
  });
  document.querySelectorAll("[data-license-filter]").forEach((btn) => {
    btn.addEventListener("click", () => {
      document.querySelectorAll("[data-license-filter]").forEach((b) => b.classList.remove("active"));
      btn.classList.add("active");
      state.licenseFilter = btn.dataset.licenseFilter;
      renderLicenses();
    });
  });

  $("purchase-search").addEventListener("input", (e) => {
    state.purchaseQuery = e.target.value;
    renderPurchases();
  });
  document.querySelectorAll("[data-purchase-filter]").forEach((btn) => {
    btn.addEventListener("click", () => {
      document.querySelectorAll("[data-purchase-filter]").forEach((b) => b.classList.remove("active"));
      btn.classList.add("active");
      state.purchaseFilter = btn.dataset.purchaseFilter;
      renderPurchases();
    });
  });

  $("customer-search").addEventListener("input", (e) => {
    state.customerQuery = e.target.value;
    renderCustomers();
  });
}

(async function boot() {
  bindUi();
  const token = localStorage.getItem(TOKEN_KEY);
  if (!token) return;
  try {
    const me = await api("me");
    showApp(me.admin?.username, me.admin?.kind || "user");
  } catch (_) {
    showLogin();
  }
})();
