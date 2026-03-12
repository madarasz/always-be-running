const fs = require('fs');
const path = require('path');
const crypto = require('crypto');

const copyTargets = [
  ['resources/assets/fonts', 'public/fonts'],
  ['resources/assets/img', 'public/img'],
  ['resources/assets/favicons', 'public'],
];

for (const [src, dest] of copyTargets) {
  const sourcePath = path.resolve(src);
  const targetPath = path.resolve(dest);

  if (!fs.existsSync(sourcePath)) {
    continue;
  }

  fs.mkdirSync(targetPath, { recursive: true });
  fs.cpSync(sourcePath, targetPath, { recursive: true });
}

function resolvePackageFile(pkgName, relativePath) {
  const pkgJsonPath = require.resolve(`${pkgName}/package.json`);
  return path.resolve(path.dirname(pkgJsonPath), relativePath);
}

const legacyJsBundleOrder = [
  resolvePackageFile('jquery', 'dist/jquery.min.js'),
  resolvePackageFile('tether', 'dist/js/tether.min.js'),
  resolvePackageFile('bootstrap', 'dist/js/bootstrap.min.js'),
  resolvePackageFile('jquery-bracket', 'dist/jquery.bracket.min.js'),
  resolvePackageFile('timepicker', 'jquery.timepicker.min.js'),
  resolvePackageFile('vue', 'dist/vue.js'),
  resolvePackageFile('axios', 'dist/axios.min.js'),
  resolvePackageFile('toastr', 'build/toastr.min.js'),
  resolvePackageFile('v-autocomplete', 'dist/v-autocomplete.js'),
  resolvePackageFile('vue-lazyload', 'vue-lazyload.js'),
  resolvePackageFile('marked', 'lib/marked.umd.js'),
  path.resolve('resources/assets/js/bootstrap-datepicker.js'),
  path.resolve('resources/assets/js/jquery.calendario.js'),
  path.resolve('resources/assets/js/ekko-lightbox.min.js'),
  path.resolve('resources/assets/js/atc.min.js'),
  path.resolve('resources/assets/js/cookieconsent.min.js'),
  path.resolve('resources/assets/js/abr-calendar.js'),
  path.resolve('resources/assets/js/abr-map.js'),
  path.resolve('resources/assets/js/abr-table.js'),
  path.resolve('resources/assets/js/abr-main.js'),
  path.resolve('resources/assets/js/abr-stats.js'),
  path.resolve('resources/assets/js/abr-matches.js'),
  path.resolve('resources/assets/js/abr-flags.js'),
  path.resolve('resources/assets/js/abr-vue.js'),
  path.resolve('resources/assets/js/tournament.table.js'),
];

const missingFiles = legacyJsBundleOrder.filter((file) => !fs.existsSync(file));
if (missingFiles.length) {
  console.error('[assets] Missing files required for legacy JS bundle:');
  for (const file of missingFiles) {
    console.error(` - ${file}`);
  }
  process.exit(1);
}

const bundledJs = legacyJsBundleOrder
  .map((file) => {
    const relativeFile = path.relative(process.cwd(), file);
    const contents = fs.readFileSync(file, 'utf8');
    return `\n/* ${relativeFile} */\n${contents}\n`;
  })
  .join(';\n');

const jsOutputDir = path.resolve('public/js');
const jsOutputPath = path.resolve(jsOutputDir, 'all.js');
fs.mkdirSync(jsOutputDir, { recursive: true });
fs.writeFileSync(jsOutputPath, bundledJs, 'utf8');

const hash = crypto.createHash('sha256').update(bundledJs).digest('hex').slice(0, 16);
const manifestPath = path.resolve(jsOutputDir, 'legacy-manifest.json');
const manifest = {
  'all.js': hash,
};
fs.writeFileSync(manifestPath, `${JSON.stringify(manifest, null, 2)}\n`, 'utf8');

console.log('[assets] Synced legacy static assets to public/.');
console.log('[assets] Built legacy JS bundle at public/js/all.js');
console.log(`[assets] Updated legacy JS manifest at public/js/legacy-manifest.json (all.js?id=${hash})`);
