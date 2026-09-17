<?php
declare(strict_types=1);
$page = 'home';
$title = 'EasyReportCreator — Excel lists from ProcessPower.dcf';
$description = 'Upload ProcessPower.dcf and issue valve, equipment, line and component lists to Excel. Plant 3D is not required — only the .dcf file.';
require __DIR__ . '/inc/header.php';
?>

<section class="hero">
  <div class="wrap hero-grid">
    <div>
      <div class="eyebrow">From ProcessPower.dcf</div>
      <h1>Every list.<br>Every property.<br><span class="accent">One export.</span></h1>
      <p class="sub">EasyReportCreator reads your <strong>ProcessPower.dcf</strong> file, lets you choose the columns that belong on the issued list, and writes English Excel with your logo, title block and revision table. <strong>Plant 3D is not needed</strong> — only the .dcf file.</p>
      <div class="hero-ctas">
        <a href="download.php" class="btn">Open the app</a>
        <a href="product.php" class="btn ghost">See how it works</a>
      </div>
      <div class="compat-row">
        <span><i class="dot"></i>ProcessPower.dcf only</span>
        <span><i class="dot"></i>Excel issue (one-way)</span>
        <span><i class="dot"></i>7-day trial</span>
      </div>
    </div>

    <div class="panel reveal">
      <div class="panel-bar">
        <span class="file">Sample project — Valve List</span>
        <div class="panel-dots"><i></i><i></i><i></i></div>
      </div>
      <div class="prop-row">
        <span class="key">Tag</span>
        <span class="val val new">HV-101</span>
        <span class="tag">Listed</span>
      </div>
      <div class="prop-row">
        <span class="key">Omschrijving</span>
        <span class="val val new">Hand valve DN80</span>
        <span class="tag">Listed</span>
      </div>
      <div class="prop-row updating">
        <span class="key">Header</span>
        <span class="val"><span class="val old">Manual fields</span><span class="val new">S88 Projectcode MNPRHN</span></span>
        <span class="tag">Filled</span>
      </div>
      <div class="prop-row">
        <span class="key">Columns</span>
        <span class="val val new">Tag, Size, Medium, LineNumber</span>
        <span class="tag">Template</span>
      </div>
      <div class="prop-row">
        <span class="key">Export</span>
        <span class="val val new">sample-project-AL.xlsx</span>
        <span class="tag">Issued</span>
      </div>
      <div class="panel-footer">
        <span>Sample project · no AutoCAD licence for listing</span>
        <span class="n">Ready to issue</span>
      </div>
    </div>
  </div>
</section>

<div class="trust">
  <div class="wrap">
    <span>Built for engineering teams issuing P&amp;ID lists from ProcessPower.dcf</span>
    <span>NL / EU — <?= h(SITE['domain']) ?></span>
  </div>
</div>

<section>
  <div class="wrap">
    <div class="section-head reveal">
      <div class="eyebrow">Why it exists</div>
      <h2>Report Creator can do this. It should not feel like this.</h2>
      <p>Valve lists, equipment lists, line lists and a Componentenlijst are still produced from the same DCF — but the interface, the header, and the column set should belong to your company standard, not to a dialog maze.</p>
    </div>
    <div class="features reveal">
      <div class="feature"><div class="body">
        <span class="key">PROJECT HEADER</span>
        <h3>Title block from Project Details</h3>
        <p>Standard fields and user-defined properties (S88 and any other category) fill the issued header, together with your logo and a revision table.</p>
      </div></div>
      <div class="feature"><div class="body">
        <span class="key">COLUMNS</span>
        <h3>Pick any class property</h3>
        <p>Browse the P&amp;ID class properties used on the project and choose which ones appear as columns. Save the set as a reusable template.</p>
      </div></div>
      <div class="feature"><div class="body">
        <span class="key">EXCEL</span>
        <h3>Issue in one direction</h3>
        <p>Export English Excel ready to send. Version 1 does not write back into the live DCF — the original project database stays untouched.</p>
      </div></div>
    </div>
    <p class="after-features"><a href="product.php" class="btn ghost">See all features →</a></p>
  </div>
</section>

<section class="proof">
  <div class="wrap">
    <div class="section-head reveal">
      <div class="eyebrow">In practice</div>
      <h2>The sample project, issued correctly.</h2>
      <p>Acceptance uses sample project MN-P-RHN-PID-0001. Upload the .dcf, choose a list, export — Plant 3D does not need to be installed or running.</p>
    </div>
    <div class="diff-card reveal">
      <div class="diff-row"><span class="k">Project name</span><span><span class="v-new">MN-P-RHN-PID-0001</span></span></div>
      <div class="diff-row"><span class="k">S88.Projectcode</span><span><span class="v-new">MNPRHN</span></span></div>
      <div class="diff-row"><span class="k">S88.Locatie</span><span><span class="v-new">Rhenen</span></span></div>
      <div class="diff-row"><span class="k">List</span><span><span class="v-old">Report Creator rcfx</span><span class="v-new">EasyReportCreator template</span></span></div>
    </div>
    <div class="diff-meta">
      <div><b>8</b>core list types</div>
      <div><b>1</b>Excel issue</div>
      <div><b>0</b>writes to live DCF</div>
    </div>
  </div>
</section>

<section id="download">
  <div class="wrap">
    <div class="section-head reveal">
      <div class="eyebrow">Hosted web app</div>
      <h2>Upload the .dcf in your browser. A local app will follow later.</h2>
      <p>Today EasyReportCreator runs as a hosted web app: upload <code>ProcessPower.dcf</code>, preview lists, export Excel. A local / desktop app may follow later — it is not available yet.</p>
    </div>
    <div class="download-card reveal">
      <div class="download-card-main">
        <div class="eyebrow">Web app</div>
        <h2>EasyReportCreator online</h2>
        <p class="download-meta">
          <span>Version <?= h(SITE['product_version']) ?></span>
          <span>| Browser</span>
          <span>| ProcessPower.dcf</span>
        </p>
        <div class="download-actions">
          <a class="btn" href="report/register.php">Start 7-day trial</a>
          <a href="download.php" class="btn ghost">Open the app</a>
        </div>
      </div>
      <div class="download-card-side">
        <div class="key">STEPS</div>
        <ul>
          <li>Create an account (7-day trial)</li>
          <li>Upload ProcessPower.dcf</li>
          <li>Choose a list → preview rows</li>
          <li>Export English Excel</li>
        </ul>
      </div>
    </div>
  </div>
</section>

<section>
  <div class="wrap">
    <div class="cta reveal">
      <div>
        <h2>Ready to stop fighting Report Creator?</h2>
        <p>EasyReportCreator is a hosted web app for English issued lists and company templates from your <strong>ProcessPower.dcf</strong> — Plant 3D is not required. A local app will follow later.</p>
      </div>
      <a href="contact.php" class="btn">Talk to us</a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/inc/footer.php'; ?>
