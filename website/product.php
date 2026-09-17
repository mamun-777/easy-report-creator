<?php
declare(strict_types=1);
$page = 'product';
$title = 'Product — EasyReportCreator | easyreportcreator.com';
$description = 'Upload ProcessPower.dcf, fill the header from Project Details, pick columns, save a template, and issue Excel. Plant 3D is not required.';
require __DIR__ . '/inc/header.php';
?>

<section class="page-head">
  <div class="wrap">
    <div class="eyebrow">Product</div>
    <h1>Upload the .dcf. Issue the list.</h1>
    <p class="sub">Everything EasyReportCreator does to turn <strong>ProcessPower.dcf</strong> into company-standard Excel lists — without AutoCAD Report Creator, and without writing back to the live database. <strong>Plant 3D is not required.</strong></p>
  </div>
</section>

<section class="tight">
  <div class="wrap">
    <div class="features reveal">
      <div class="feature"><div class="body">
        <span class="key">OPEN</span>
        <h3>ProcessPower.dcf only</h3>
        <p>Upload the .dcf in the browser. The app reads Project Details and engineering tables. Plant 3D does not need to be installed or running.</p>
      </div></div>
      <div class="feature"><div class="body">
        <span class="key">HEADER</span>
        <h3>Project Details + custom properties</h3>
        <p>Standard name, description and number, plus every user-defined category (for example S88 on the sample project) can appear in the title block with your logo and revision rows.</p>
      </div></div>
      <div class="feature"><div class="body">
        <span class="key">COLUMNS</span>
        <h3>Class property catalogue</h3>
        <p>Select any standard or user-defined property available on the list’s class in the project. The selection is not a hard-coded handful of fields.</p>
      </div></div>
      <div class="feature"><div class="body">
        <span class="key">TEMPLATES</span>
        <h3>Company standards</h3>
        <p>Save column set, sort, header fields and revision table. Reload the standard the next time you issue the same list type.</p>
      </div></div>
      <div class="feature"><div class="body">
        <span class="key">LISTS</span>
        <h3>The lists you already produce</h3>
        <p>Valve, control valve, equipment, line, line summary, instrument, drawing, and Componentenlijst — proven on the sample project.</p>
      </div></div>
      <div class="feature"><div class="body">
        <span class="key">EXCEL</span>
        <h3>One-way issued workbook</h3>
        <p>English Excel with logo, title block and revision table. Version 1 does not import changes back into the live DCF.</p>
      </div></div>
    </div>
  </div>
</section>

<section>
  <div class="wrap">
    <div class="section-head reveal">
      <div class="eyebrow">Workflow</div>
      <h2>Five steps, not a Report Creator session.</h2>
      <p>You only need the .dcf file. Excel stays the issued document. A local app will follow later.</p>
    </div>
    <div class="steps reveal">
      <div class="step">
        <div class="step-num">01</div>
        <h3>Upload the .dcf</h3>
        <p>Select ProcessPower.dcf. The app reads identity, drawings and counts from the database.</p>
      </div>
      <div class="step">
        <div class="step-num">02</div>
        <h3>Choose the list</h3>
        <p>Valve, equipment, line, instrument, drawing, Componentenlijst — or a saved company template.</p>
      </div>
      <div class="step">
        <div class="step-num">03</div>
        <h3>Pick columns &amp; header</h3>
        <p>Tick the properties that belong on this issue. Confirm logo, Project Details fields and revision rows.</p>
      </div>
    </div>
  </div>
</section>

<section class="tight">
  <div class="wrap">
    <div class="section-head reveal">
      <div class="eyebrow">Requirements</div>
      <h2>What you need.</h2>
    </div>
    <div class="spec-table reveal">
      <div class="spec-row"><span class="k">Browser</span><span class="v">Modern browser with access to easyreportcreator.com</span></div>
      <div class="spec-row"><span class="k">Input file</span><span class="v">ProcessPower.dcf only — Plant 3D not required</span></div>
      <div class="spec-row"><span class="k">AutoCAD licence</span><span class="v">Not required for listing and Excel export</span></div>
      <div class="spec-row"><span class="k">Public site</span><span class="v">easyreportcreator.com — product pages + report app</span></div>
      <div class="spec-row"><span class="k">Local app</span><span class="v">Will follow later — not available yet</span></div>
      <div class="spec-row"><span class="k">Language</span><span class="v">English issued lists (Version 1)</span></div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/inc/footer.php'; ?>
