import {google} from "googleapis";

const container = document.getElementById('gdrive-list');

if (container) {
  const CLIENT_ID = '558906194872-fbavgftk9nato7cq0gqp55g02j9ldrvc.apps.googleusercontent.com';
  const CLIENT_SECRET = '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';
  const REDIRECT_URL = 'http://muse.irec.tokyo';
  const API_KEY = 'AIzaSyAPn3ud2DFUpeH25NQtaCvGgc7MEewN4XY';

  const oauth2Client = new google.auth.OAuth2(
    CLIENT_ID,
    CLIENT_SECRET,
    REDIRECT_URL
  );

  const drive = google.drive({
    version: 'v2',
    auth: oauth2Client
  });

  console.log(drive.files.list())
}
