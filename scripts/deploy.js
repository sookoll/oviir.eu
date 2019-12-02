require('dotenv').config()
const FtpDeploy = require('ftp-deploy')
const ftpDeploy = new FtpDeploy()
const args = process.argv.slice(2)

const config = {
  user: process.env.FTP_USER,
  // Password optional, prompted if none given
  password: process.env.FTP_PASS,
  host: process.env.FTP_HOST,
  port: 21,
  localRoot: __dirname + '/../src',
  remoteRoot: '/htdocs/dev',
  // include: ["*", "**/*"],      // this would upload everything except dot files
  // include: ['content/**/*', 'plugins/**/*', 'themes/**/*', 'vendor/**/*', '.htaccess', 'index.php', 'config/*.yml'],
  include: ['content/**/*', 'plugins/**/*', 'themes/**/*', 'vendor/**/*', '.htaccess', 'index.php'],
  // e.g. exclude sourcemaps, and ALL files in node_modules (including dot files)
  exclude: [],
  // delete ALL existing files at destination before uploading, if true
  deleteRemote: false,
  // Passive mode is forced (EPSV command is not sent)
  forcePasv: true
}

if (args.length) {
  config.include = args
}

ftpDeploy.on("uploading", function(data) {
  //console.log(data); // same data as uploading event
})

ftpDeploy.on("uploaded", function(data) {
  console.log(
    data.transferredFileCount,
    data.totalFilesCount,
    data.filename
  )
})

ftpDeploy.on("log", function(data) {
  //console.log(data); // same data as uploading event
})

ftpDeploy.on("upload-error", function(data) {
  console.log(data.err); // data will also include filename, relativePath, and other goodies
})

ftpDeploy
  .deploy(config)
  .then(res => console.log("done"))
  .catch(err => console.log(err))
