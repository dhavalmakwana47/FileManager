$(function () {
  const fileManager = $("#file-manager").dxFileManager({
    name: "fileManager",
    fileProvider: [],
    height: 450,
    width: 800,
    itemView: { mode: "thumbnails" },
    permissions: {
      create: false,
      rename: true
    },
    toolbar: {
      items: [
        { name: "upload", visible: true },
        { name: "refresh", visible: true },
        {
          name: "create-folder",
          icon: "plus",
          html: "<i class='dx-icon dx-icon-add'></i><span class='dx-button-text'>New Folder</span>",
          visible: true
        }

      ],
      fileSelectionItems: [
        {
          name: "delete-folder", // Custom delete button
          icon: "trash",
          html: "<i class='dx-icon dx-icon-trash'></i><span class='dx-button-text'>Delete</span>",
          visible: true,
          onClick: function () {
            customDeleteHandler();
          }
        }, 'clearSelection'
      ]
    },
    onSelectionChanged: function (e) {
      let selectedItems = e.selectedItems;

      let canDelete = selectedItems.length > 0 && selectedItems.every(item => item.dataItem.permissions && item.dataItem.permissions.delete);
      fileManager.option("toolbar.fileSelectionItems[0].visible", canDelete)
    },
    onToolbarItemClick: function (e) {
      if (e.itemData.name === "create-folder") {
        e.cancel = true;
        createNewFolder();
      }

    }
  }).dxFileManager("instance");

  function customDeleteHandler() {
    let selectedItems = fileManager.getSelectedItems();

    console.log(selectedItems)
    if (selectedItems.length === 0) {
      alert("No items selected.");
      return;
    }
  }

  const headers = {
    "Content-Type": "application/json",
    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
  };

  // ✅ Fetch File Manager Data
  async function fetchFileManagerData() {
    try {
      const response = await fetch(getFileMangerRoute);
      const data = await response.json();
      fileManager.option("fileSystemProvider", data);

    } catch (error) {
      console.error("Error fetching data:", error);
    }
  }

  // ✅ Custom Function to Create a New Folder
  function createNewFolder() {
    $('#createFolderForm')[0].reset();
    $('#role-select').change();
    $('#createFolderModal').modal('show');
  }

  // Handle folder creation
  $('#createFolderForm').on('submit', function (e) {
    e.preventDefault();

    let csrfToken = $('meta[name="csrf-token"]').attr('content');
    const currentDir = fileManager.getCurrentDirectory();
    const parentId = currentDir.dataItem?.id || "";

    let formData = new FormData(this);
    formData.append("parent_id", parentId); // Append parent_id

    $.ajax({
      url: createFolderRoute, // Ensure this route is correctly defined
      type: "POST",
      data: formData,
      headers: headers,
      processData: false,  // ✅ Prevent jQuery from processing data
      contentType: false,  // ✅ Prevent jQuery from setting Content-Type
      success: function (response) {
        if (response.success) {
          $('#createFolderModal').modal('hide'); // Close modal
          fetchFileManagerData(); // Refresh file manager
        } else {
          alert(response.message); // Show error message
        }
      },
      error: function (xhr, status, error) {
        console.error("Error:", xhr.responseText); // Log error response
        alert("An unexpected error occurred. Please try again.");
      }
    });
  });



  // Initialize File Manager Data
  fetchFileManagerData();
});
