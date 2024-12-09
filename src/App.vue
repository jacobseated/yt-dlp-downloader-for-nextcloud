<template>
  <NcAppContent>
    <div id="downloader">
      <div>
        <h1>Media Downloader (yt-dlp based)</h1>
        <p>
          Note. Media will be stored in the "downloader" folder of your
          Nextcloud root.
        </p>
        <NcTextField
          v-model="mediaUrl"
          class="inputBox"
          label="Media URL"
          :disabled="isLoading"
        />
        <p>Note: Write the filename without file-extension.</p>
        <NcTextField
          v-model="fileName"
          class="inputBox"
          label="some-file-name"
          :disabled="isLoading"
        />

        <p>Note: The "default" options just downloads the video.</p>
        <NcSelect
          v-model="fileFormat"
          :disabled="isLoading"
          class="formatSelect"
          :options="audioFormats"
        />

        <NcButton
          class="downloadBtn"
          :disabled="isLoading"
          type="success"
          @click="downloadMedia"
        >
          Download
        </NcButton>
        <NcLoadingIcon v-if="isLoading" :size="64" appearance="dark" />
      </div>
    </div>
  </NcAppContent>
</template>

<script>
import NcAppContent from "@nextcloud/vue/dist/Components/NcAppContent.js";
import NcTextField from "@nextcloud/vue/dist/Components/NcTextField.js";
import NcButton from "@nextcloud/vue/dist/Components/NcButton.js";
import NcSelect from "@nextcloud/vue/dist/Components/NcSelect.js";
import NcLoadingIcon from "@nextcloud/vue/dist/Components/NcLoadingIcon.js";
import axios from "axios";

export default {
  name: "App",
  components: {
    NcAppContent,
    NcTextField,
    NcButton,
    NcSelect,
    NcLoadingIcon,
  },
  data() {
    return {
      fileName: "",
      mediaUrl: "",
      isLoading: false,
      fileFormat: "default",
      audioFormats: [
        { id: "default", label: "Default (Download / no conversion)" },
        { id: "opus", label: "Opus (.ogg)" },
        { id: "vorbis", label: "Vorbis (.ogg)" },
        { id: "mp3", label: "mp3 (.mp3)" },
      ],
    };
  },
  methods: {
    downloadMedia() {
      this.isLoading = true;
      axios
        .post(
          "/ocs/v2.php/apps/downloader/dlfile?format=json",
          {
            mediaUrl: this.mediaUrl,
            fileName: this.fileName,
            fileFormat: this.fileFormat,
          },
          {
            headers: {
              "Content-Type": "application/json",
              "OCS-APIREQUEST": true,
            },
          }
        )
        .then((response) => {
          this.showNotification(response.data.ocs.data.message, "info");
        })
        .catch((error) => {
          if (error?.response?.data?.ocs?.data?.message) {
            this.showNotification(
              error.response.data.ocs.data.message,
              "error"
            );
          } else {
            this.showNotification("An unexpected error occurred", "error");
          }
        })
        .finally(() => {
          this.isLoading = false;
        });
    },
    showNotification(message, type = "info") {
      if (typeof OC !== "undefined" && OC.Notification) {
        OC.Notification.show(message, { type, timeout: 5000 });
      } else {
        console.error("OC.Notification is not available");
      }
    },
  },
};
</script>

<style scoped lang="scss">
#downloader {
  display: flex;
  justify-content: center;
  margin: 16px;

  h1 {
    font-size: 2rem;
    margin: 0 0 2rem;
  }
  p {
    padding: 0.5rem 0 0;
  }
  .inputBox {
    display: block;
  }
  .downloadBtn {
    margin: 1rem 0 0;
  }
  .formatSelect {
    margin: 1rem 0 0;
  }
}
</style>
