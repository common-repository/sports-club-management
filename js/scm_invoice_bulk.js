wp.api.loadPromise.done(function() {
	
var scmInvoiceBulkCat1 = document.getElementById("bulk_member_category");
var scmInvoiceBulkIssueDate1 = document.getElementById("issuedate");
var scmInvoiceBulkDueDate1 = document.getElementById("duedate");
var scmInvoiceBulkService1 = document.getElementById("service");
var scmInvoiceBulkAmount1 = document.getElementById("amount");
var scmInvoiceBulkCredit1 = document.getElementById("credit");
var scmInvoiceBulkDebet1 = document.getElementById("debet");
var scmInvoiceBulkCustom11 = document.getElementById("custom1");
var scmInvoiceBulkCustom21 = document.getElementById("custom2");
var scmInvoiceBulkPrefix1 = document.getElementById("prefix");

class InvoiceList extends List {
	constructor () {
		super();
		
		this.memberCategory = -1;
		this.issueDate = "";
		this.dueDate = "";
		this.service = "";
		this.amount = 0;
		this.creditDebet = "Credit";
		this.prefix = "";
		this.custom1 = "";
		this.custom2 = "";
	}
	
	done() {
		location.replace(scm_invoice_bulk_globals.url_done);
	}
	
	getFormData() {
		var today = new Date();
		var validation = { valid : true, errorStr : "" };
		
		this.memberCategory 	= scmInvoiceBulkCat1.value;
		this.issueDate 			= scmInvoiceBulkIssueDate1.value;
		this.dueDate 			= scmInvoiceBulkDueDate1.value;
		this.service 			= scmInvoiceBulkService1.value;
		this.amount 			= scmInvoiceBulkAmount1.value;
		if ( scmInvoiceBulkCredit1.checked ) {
			this.creditDebet = "Credit";
		}
		if ( scmInvoiceBulkDebet1.checked ) {
			this.creditDebet = "Debet";
		}
		this.prefix		 		= scmInvoiceBulkPrefix1.value + "." + today.getFullYear() + "-" + (parseInt(today.getMonth()) + 1).toString() + "-" + today.getDate();
		this.custom1			= scmInvoiceBulkCustom11.value;
		this.custom2			= scmInvoiceBulkCustom21.value;	
		
		if (this.memberCategory < 0) {
			validation = { valid : false, errorStr : "select member category" };
		}
		
		return validation;
	}
	
	addEntries(jsonResponse) {
		var _entries = this.entries;
		jsonResponse.forEach(function(item){
			var entry = new InvoiceEntry(
				  item.name + ", " + item.first_name + " " + item.middle_name 
				, item.id
			);
			_entries.push( entry );
		});
	}
		
	render(all) {
		var headers = [ "issue date", "due date", "service", "amount", "credit / debet", "custom 1", "custom 2", "prefix"];
		var data    = [ this.issueDate, this.dueDate, this.service, this.amount, this.creditDebet, this.custom1, this.custom2, this.prefix];
		
		var html;
		
		html = "<tr><th></th><th>Name</th><th>" + headers.join("</th><th>") + "</th></tr>";
		this.entries.sort(function(a, b){ 
			if (a.name < b.name) { return -1; } else { return 1; } 
		}).forEach(function(item, idx){
				html += "<tr>" + item.render(idx, all, "<td>" + data.join("</td><td>") + "</td>") + "</tr>";
		});
		
		return "<table>" + html + "</table>";
	}
		
	getSelectedEntry(index) {
		var memberid 		= this.entries[index].id;
		var object 			= super.getSelectedEntry(index);
		
		object.title		= this.prefix + "." + memberid;
		object.member_id	= memberid;
		object.issue_date	= this.issueDate;
		object.due_date		= this.dueDate;
		object.service		= this.service;
		object.amount		= this.amount;
		object.credit_debet	= this.creditDebet;
		
		return object;
	}

	populatePage(this_list, pagenumber, successHandler, errorHandler) {
		var members = new wp.api.collections.Members();

		members.fetch( { 
			  data		: { page : pagenumber, member_categories : this_list.memberCategory } 
			, success 	: successHandler 
			, error 	: errorHandler
		}); 
	}
	
	processEntry(entry, successHandler, errorHandler) {
		var invoice = new wp.api.models.Invoices();
		
		invoice.save( entry
			, { success : successHandler, error : errorHandler }	
		);
	}
}


class InvoiceEntry extends Entry {
	
	constructor (name, id) {
		super();
		
		this.name = name; 
		this.id = id;
	}
	
	render (idx, renderAll, renderedData) {
		return super.render(idx, renderAll, "<td>" + this.name + "</td>" + renderedData);
	}
	
}

var wizard = new Subject( new InvoiceList() );
wizard.start();

});