import fs from 'fs';
import path from 'path';
import { execSync } from 'child_process';
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
    const srcName = `gb-fullcalendar.zip`;
    const zipName = `gb-fullcalendar-${suffix}.zip`;

    fs.mkdirSync(outputDir, { recursive: true });

    execSync('wp-scripts plugin-zip');

    fs.renameSync(srcName, path.join(outputDir, zipName));

    console.log(`Created ${zipName}`);
}

// CLI usage
const isSnapshot = process.argv.includes('--snapshot');
await zip(isSnapshot);
