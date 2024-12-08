# Downloader

A template to get started with Nextcloud app development.

## Server Dependencies

- **yt-dlp** needs to be installed at the server at `/usr/bin/yt-dlp` E.g.

  Using pip:
  ```console
  pip install --upgrade yt-dlp
  ```
  
  Or just install the binary:
  ```console
  wget -O /usr/bin/yt-dlp https://github.com/yt-dlp/yt-dlp/releases/latest/download/yt-dlp
  chmod +x /usr/bin/yt-dlp
  ```

- python3
- ffmpeg

You should be able to easily install both **python3** and **ffmpeg** using the package manager of your operating system. E.g:

### Ubuntu
```console
apt install python3 python3-pip ffmpeg
```
### Alpine
```console
apk add --no-cache python3 py3-pip ffmpeg
```
