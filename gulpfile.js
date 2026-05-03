import gulp from 'gulp';
import gulpRename from 'gulp-rename';
import gulpZip from 'gulp-zip';
import * as packageJson from './package.json' with {type: 'json'};

export async function zip(snapshot = false) {
    let suffix;
    if (snapshot) {
        suffix = await new Promise((resolve, reject) => {
            const {exec} = require('child_process');
            exec('git rev-parse --abbrev-ref HEAD', (err, stdout, stderr) => {
                if (err) {
                    reject(err);
                }
                resolve(stdout.trim());
            });
        });
        suffix = suffix.replace(/[\\~#%&*{}\/:<>?|"]/g, '') + '-SNAPSHOT';
    } else {
        suffix = 'v' + packageJson.version;
    }
    return gulp.src([
        'build/*',
        'php/*',
        'res/*',
        'gb-fullcalendar.php',
        'package.json',
        'README.md',
        'CHANGELOG.md',
        'LICENSE.md',
    ], {base: './'})
        .pipe(gulpRename(function (file) {
            file.dirname = 'gb-fullcalendar/' + file.dirname;
        }))
        .pipe(gulpZip(`gb-fullcalendar-${suffix}.zip`))
        .pipe(gulp.dest('dist'));
}

export async function zipSnapshot() {
    await zip(true);
}
