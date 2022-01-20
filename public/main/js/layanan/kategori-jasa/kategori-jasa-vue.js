$(document).ready(function() {
	const kategoriJasaApp = new Vue({
		el: '#kategori-jasa-app',
		data: {
			listData: [],
			searchTxt: '',
			titleModal: '',   stateModal: '',
			msgContent: '',   confirmContent: '',
			idKategori: '',   kategoriJasa: '',
			kategoriErr1: false, touchedForm: false,
			beErr: false,     msgBeErr: '',
			columnStatus: {
				category_name: 'none',
				created_by: 'none',
				created_at: 'none'
			},
			paramUrlSetup: {
				orderby:'',
				column: '',
				keyword: ''
			}
		},
		mounted() {
			if (role.toLowerCase() != 'admin') {
				$('.columnAction').hide();
				$('.section-left-box-title .btn').hide();
			}
			this.getData();
		},
		computed: {
			validateSimpanKateJasa: function() {
				return this.kategoriErr1 || this.beErr || !this.touchedForm;
			}
		},
		methods: {
			openFormAdd: function() {
				if (role.toLowerCase() != 'dokter') {
					this.stateModal = 'add';
					this.titleModal = 'Tambah Kategori Jasa';
					this.refreshVariable();
					$('#modal-kategori-jasa').modal('show');
				}
			},
			openFormUpdate: function(item) {
				this.stateModal = 'edit';
				this.titleModal = 'Ubah Kategori Jasa';
				this.refreshVariable();
				this.idKategori = item.id;
				this.kategoriJasa = item.category_name;
				$('#modal-kategori-jasa').modal('show');
			},
			openFormDelete: function(item) {
				this.stateModal = 'delete';
				this.idKategori = item.id;
				this.confirmContent = 'Anda yakin ingin menghapus Kategori Jasa ini?';
				$('#modal-confirmation').modal('show');
			},
			kategoriJasaKeyup: function() {
				this.validationForm();
			},
			submitKateJasa: function() {
				if (this.stateModal === 'add') {
					const form_data = new FormData();
					form_data.append('NamaKategoriJasa', this.kategoriJasa);

					this.processSave(form_data);
				} else if (this.stateModal === 'edit') {
					$('#modal-confirmation').modal('show');
					this.confirmContent = 'Anda yakin untuk mengubah kategori jasa ?';
				}
			},
			submitConfirm: function() {

				if (this.stateModal === 'edit') {
					const request = {
						id: this.idKategori,
						NamaKategoriJasa: this.kategoriJasa,
					};

					this.processEdit(request);
				} else {
					this.processDelete({ id: this.idKategori });
				}
			},
			onOrdering: function(e) {
				this.columnStatus[e] = (this.columnStatus[e] == 'asc') ? 'desc' : 'asc';
				if (e === 'category_name') {
					this.columnStatus['created_by'] = 'none';
					this.columnStatus['created_at'] = 'none';
				}  else if (e === 'created_by') {
					this.columnStatus['category_name'] = 'none';
					this.columnStatus['created_at'] = 'none';
				} else {
					this.columnStatus['category_name'] = 'none';
					this.columnStatus['created_by'] = 'none';
				}

				this.paramUrlSetup.orderby = this.columnStatus[e];
				this.paramUrlSetup.column = e;
				this.getData();
			},
			onSearch: function() {
				this.paramUrlSetup.keyword =  this.searchTxt;
				this.getData();
			},
			getData: function() {
				$('#loading-screen').show();
				axios.get($('.baseUrl').val() + '/api/kategori-jasa', { params: this.paramUrlSetup, headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` }})
					.then(resp => {
						const getRespData = resp.data;
						getRespData.map(item => {
							item.isRoleAccess = (role.toLowerCase() != 'admin') ? false : true;
							return item;
						});
						this.listData = getRespData;
					})
					.catch(err => {
						if (err.response.status === 401) {
							localStorage.removeItem('vet-clinic');
							location.href = $('.baseUrl').val() + '/masuk';
						}
					})
					.finally(() => {
						$('#loading-screen').hide();
					});
			},
			processSave: function(form_data) {
				$('#loading-screen').show();
				axios.post($('.baseUrl').val() + '/api/kategori-jasa', form_data, { headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` }})
				.then(resp => {
					if(resp.status == 200) {
						this.msgContent = 'Berhasil Menambah Kategori Jasa';
						$('#msg-box').modal('show');

						setTimeout(() => {
							$('#modal-kategori-jasa').modal('toggle');
							this.refreshVariable();
							this.getData();
						}, 1000);
					}
				})
				.catch(err => {
					if (err.response.status === 422) {
						this.msgBeErr = '',
						err.response.data.errors.forEach((element, idx) => {
							this.msgBeErr += element + ((idx !== err.response.data.errors.length - 1) ? '<br/>' : '');
						});
						this.beErr = true;

					} else if (err.response.status === 401) {
						localStorage.removeItem('vet-clinic');
	          location.href = $('.baseUrl').val() + '/masuk';
					}
				})
				.finally(() => {
					$('#loading-screen').hide();
				});
			},
			processEdit: function(form_data) {
				$('#loading-screen').show();
				axios.put($('.baseUrl').val() + '/api/kategori-jasa', form_data, { headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` }})
				.then(resp => {
					if(resp.status == 200) {
						$('#modal-confirmation').modal('toggle');

						this.msgContent = 'Berhasil Mengubah Kategori Jasa';
						$('#msg-box').modal('show');

						setTimeout(() => {
							$('#modal-kategori-jasa').modal('toggle');
							this.refreshVariable();
							this.getData();
						}, 1000);
					}
				})
				.catch(err => {
					if (err.response.status === 422) {
						this.msgBeErr = '',
						err.response.data.errors.forEach((element, idx) => {
							this.msgBeErr += element + ((idx !== err.response.data.errors.length - 1) ? '<br/>' : '');
						});
						this.beErr = true;
						$('#modal-confirmation').modal('toggle');

					} else if (err.response.status === 401) {
						localStorage.removeItem('vet-clinic');
	          location.href = $('.baseUrl').val() + '/masuk';
					}
				})
				.finally(() => {
					$('#loading-screen').hide();
				});
			},
			processDelete: function(form_data) {
				axios.delete($('.baseUrl').val() + '/api/kategori-jasa', { params: form_data, headers: { 'Authorization': `Bearer ${token}` } })
				.then(resp => {
					if (resp.status == 200) {
						$('#modal-confirmation').modal('toggle');

						this.msgContent = 'Berhasil menghapus kategori jasa';
						$('#msg-box').modal('show');
						this.getData();
					}
				})
				.catch(err => {
					if (err.response.status === 401) {
						localStorage.removeItem('vet-clinic');
	          location.href = $('.baseUrl').val() + '/masuk';
					}
				})
			},
			validationForm: function() {
				this.touchedForm = true; this.beErr = false;
				this.kategoriErr1 = (!this.kategoriJasa) ? true : false; 
			},
			refreshVariable: function() {
				this.kategoriJasa = ''; this.kategoriErr1 = false;
				this.beErr = false; this.touchedForm = false;
			}
		}
	})
});