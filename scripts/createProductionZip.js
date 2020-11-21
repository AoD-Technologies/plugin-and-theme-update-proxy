const fs = require('fs')
const path = require('path')

const archiver = require('archiver')

const projectDir = path.normalize(path.join(__dirname, '..'))

const output = fs.createWriteStream(path.join(projectDir, 'plugin-and-theme-update-proxy.zip'))
const archive = archiver('zip', {
    zlib: {
        level: 9
    }
})

// listen for all archive data to be written
// 'close' event is fired only when a file descriptor is involved
output.on('close', function() {
    console.log(archive.pointer() + ' total bytes.')
    console.log('archiver has been finalized and the output file descriptor has closed.')
})

// This event is fired when the data source is drained no matter what was the data source.
// It is not part of this library but rather from the NodeJS Stream API.
// @see: https://nodejs.org/api/stream.html#stream_event_end
output.on('end', function() {
    console.log('Data has been drained.')
})

// good practice to catch warnings (ie stat failures and other non-blocking errors)
archive.on('warning', function(err) {
if (err.code === 'ENOENT') {
    // log warning
} else {
    // throw error
    throw err
}
})

// good practice to catch this error explicitly
archive.on('error', function(err) {
    throw err
})

// pipe archive data to the file
archive.pipe(output)

const globOptions = {
    cwd: projectDir,
    ignore: '**/*.map'
}

const entryOptions = {
    prefix: 'plugin-and-theme-update-proxy'
}

archive.glob('assets/**/*', globOptions, entryOptions)
archive.glob('build/static/**/*', globOptions, entryOptions)
archive.glob('includes/**/*', globOptions, entryOptions)
archive.glob('*.txt', globOptions, entryOptions)
archive.glob('*.php', globOptions, entryOptions)

archive.finalize()
