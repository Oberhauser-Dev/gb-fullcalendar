import fs from 'fs';
import path from 'path';
import { execSync } from 'child_process';
import archiver from 'archiver';
import packageJson from '../package.json' with { type: 'json' };

async function getSuffix(snapshot) {
    if (snapshot) {
        let branch = execSync('git rev-parse --abbrev-ref HEAD')
            .toString()
            .trim();

        branch = branch.replace(/[\\~#%&*{}\/:<>?|"]/g, '') + '-SNAPSHOT';
        return branch;
    } else {
        return 'v' + packageJson.version;
    }
}

async function zip(snapshot = false) {
    const suffix = await getSuffix(snapshot);
    const outputDir = 'dist';
    const zipName = `gb-fullcalendar-${suffix}.zip`;

    fs.mkdirSync(outputDir, { recursive: true });

    const output = fs.createWriteStream(path.join(outputDir, zipName));
    const archive = archiver('zip', { zlib: { level: 9 } });

    archive.pipe(output);

    const files = [
        'build',
        'php',
        'res',
        'gb-fullcalendar.php',
        'package.json',
        'README.md',
        'CHANGELOG.md',
        'LICENSE.md',
    ];

    for (const file of files) {
        const fullPath = path.resolve(file);
        if (!fs.existsSync(fullPath)) continue;

        const stats = fs.statSync(fullPath);

        if (stats.isDirectory()) {
            archive.directory(fullPath, `gb-fullcalendar/${file}`);
        } else {
            archive.file(fullPath, { name: `gb-fullcalendar/${file}` });
        }
    }

    await archive.finalize();

    console.log(`Created ${zipName}`);
}

// CLI usage
const isSnapshot = process.argv.includes('--snapshot');
await zip(isSnapshot);
