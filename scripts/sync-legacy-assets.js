const fs = require('fs');
const path = require('path');

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

console.log('[assets] Synced legacy static assets to public/.');
