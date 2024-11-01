var scmBulkStep = [
	document.getElementById("scm_bulk_item1"),
	document.getElementById("scm_bulk_item2"),
	document.getElementById("scm_bulk_item3"),
	document.getElementById("scm_bulk_item4")
];

var scmBulkMessage = document.getElementById("scm_bulk_message");

var scmStep1 = document.getElementById("scm_bulk_step1");
var scmBulkList = document.getElementById("scm_bulk_list");

var scmPostStatus = document.getElementById("scm_bulk_control_poststatus");
var scmCheckBoxes = document.getElementById("scm_bulk_control_checkboxes");
var scmConfirm = document.getElementById("scm_bulk_control_confirm");

// Status constructor
function Data2() {
	this.postStatus = "draft";
	
	this.getData = function() {
		if (document.getElementById("publish_step2").checked) {
			this.postStatus = "publish";
		} else if (document.getElementById("draft_step2").checked) {
			this.postStatus = "draft";
		}
		
		return true;
	}
}

// List class
class List {
	
	constructor() {
		this.entries 	= [];
		this.data2 		= new Data2; 
		this.position 	= 0;
	}
	
	reset() {
		this.entries 	= [];
		this.data2 		= new Data2; 
		this.position 	= 0;
	}
	
	done() {
		// to be overriden
	}
	
	getFormData() {
		// MUST BE OVERRIDEN by subclass;
		return { valid : false, errorStr : "subclass shall override" };
	}
	
	getData2() {
		return this.data2.getData();
	}
	
	addEntries(jsonResponse) {
		// MUST BE OVERRIDEN by subclass;
	}
	
	selectAll(param) {
		this.entries.forEach(function(item){
			item.selected = param;
		}); 
	}
	
	select() {
		this.entries.forEach(function(item, idx){
			item.select(idx);
		}); 
	}
	
	resetIterator() {
		this.position = 0;
	}
	
	getIteratorNext() {
		while ( this.position < this.entries.length ) {
			if ( this.entries[this.position].selected && !this.entries[this.position].processed ) {
				return this.position;
			} else {
				this.position++;
			}
		}		
		return null;
	}
	
	getSelectedEntry(index) {
		return { status	: this.data2.postStatus };
	}
	
	markEntryProcessed() {
		this.entries[this.position].markProcessed();
	}
	
	render(all) {
		return "";
	}
	
	populatePage(this_list, pagenumber, successHandler, errorHandler) {
		// MUST BE OVERRIDEN by subclass;
	}
	
	populate(subject) {
		var pagesRequested 	= 1;
		var _list 			= this;
			
		var errorHandler = function(collection, xhr, options){
			subject.setMessage("error", "Error: " + xhr.statusText + " (" + xhr.status + ") - " + xhr.responseJSON.message);
			subject.networkAccess(false);
		};
		
		var successHandler = function(collection, response, options){

			_list.addEntries(response);			
			subject.notify();
			
			if ( pagesRequested < collection.state.totalPages ) {
				pagesRequested++;
				
				_list.populatePage(_list, pagesRequested, successHandler, errorHandler);
				
			} else {
				subject.setMessage("none", "");
				subject.networkAccess(false);
			}				
		};
		
		subject.setMessage("info", "Please wait, loading data...");
		subject.networkAccess(true);
		
		_list.populatePage(_list, pagesRequested, successHandler, errorHandler);		
	}
	
	processEntry(entry, successHandler, errorHandler) {
		// MUST BE OVERRIDEN by subclass;
	}
	
	process(subject) {
		var entry     	= null;
		var index       = null;
		var _list 		= this;
		
		var errorHandler = function(model, xhr, options){
			subject.setMessage("error", "Error: " + xhr.statusText + " (" + xhr.status + ") - " + xhr.responseJSON.message);
			subject.networkAccess(false);
		};
		
		var successHandler = function(model, response, options){
			
			_list.markEntryProcessed();
			subject.notify();
			
			index = _list.getIteratorNext();
			
			if (index != null) {
				entry = _list.getSelectedEntry(index);
				_list.processEntry(entry, successHandler, errorHandler);
			} else {
				subject.setMessage("none", "");
				subject.networkAccess(false);
			}
		};
		
		_list.resetIterator();
		index = _list.getIteratorNext();

		if (index != null) {
			subject.setMessage("info", "Please wait, processing data...");
			subject.networkAccess(true);
			
			entry = _list.getSelectedEntry(index);
			_list.processEntry(entry, successHandler, errorHandler);
		}

	}
	
}

// Entry class
class Entry {
	
	constructor () {
		this.selected = true;
		this.processed = false;	
	}
	
	select (idx) {
		if ( document.getElementById("scm_bulk_cb_list-" + idx + "").checked ) {
			this.selected = true;
		} else {
			this.selected = false;
		}
	}
	
	markProcessed () {
		this.processed = true;
	}
	
	render (idx, renderAll, renderedData) {
		var html = "";
		
		if (renderAll || this.selected) {
			html += "<td>";
			if (renderAll) {
				html += "<input type='checkbox' id='scm_bulk_cb_list-" + idx + "'";
				if (this.selected) {
					html += "checked";
				}
				html += ">";
			} 
			html += "</td>";
			if (this.processed) {
				html += "<td>processed</td>";
			} else {
				html += renderedData;
			}
		}

		return html;
	}
	
}

// State class
class State {
	constructor (subject) {
		this.subject = subject;
	}
	
	next () {
		this.subject.setMessage("none", "");
	}
	
	previous () {
		this.subject.setMessage("none", "");
	}
	
	renderStep () {
		scmBulkStep.forEach(function(item,idx) {
			item.style.fontWeight = "normal";
		});
		scmPostStatus.style.display = "none";
		scmConfirm.style.display = "none";
		scmCheckBoxes.style.display = "none";
		scmStep1.style.display = "none";
	}
	
	renderPreviousBtn () {
		this.subject.prevBtn.disabled = this.subject.busy; 
		this.subject.prevBtn.innerHTML = scm_bulk_globals.map_strings["previous_step"];
	}
	
	renderNextBtn () {
		this.subject.nextBtn.disabled = this.subject.busy;
		this.subject.nextBtn.innerHTML = scm_bulk_globals.map_strings["next_step"];		
	}
	
	renderList (list, all) {
		scmBulkList.style.display = "block";
		scmBulkList.innerHTML = list.render(all);
	}
}

class State0 extends State {
	constructor (subject) {
		super(subject);
	}

	next () {
		var validation = this.subject.list.getFormData();
		if (validation.valid) {
			super.next();
			this.subject.setState( new State1(this.subject) ); 
			this.subject.list.populate(this.subject);
		} else {
			this.subject.setMessage("error", validation.errorStr);
			this.subject.notify();
		}
	}
	
	previous () {
		// empty
	}

	renderStep () {
		super.renderStep();
		scmStep1.style.display = "block";
		scmBulkStep[0].style.fontWeight = "bold";
	}
	
	renderPreviousBtn () {
		super.renderPreviousBtn();
		this.subject.prevBtn.disabled = true;
	}
	
	renderList (list, all) {
		scmBulkList.style.display = "none";
	}

}

class State1 extends State {
	constructor (subject) {
		super(subject);
	}

	next () {
		var valid = this.subject.list.getData2(); 
		if (valid) {
			super.next();
			this.subject.list.select();
			this.subject.setState( new State2(this.subject) );
		} 
	}
	
	previous () {
		super.previous();
		this.subject.list.reset();
		this.subject.setState( new State0(this.subject) );
	}
	
	renderStep () {
		super.renderStep();
		scmBulkStep[1].style.fontWeight = "bold";
		scmPostStatus.style.display = "block";
		scmCheckBoxes.style.display = "block";
	}
	
	renderList (list, all) {
		super.renderList(list, true);
	}
}

class State2 extends State {
	constructor (subject) {
		super(subject);
	}

	next () {
		if ( document.getElementById("scm_bulk_confirm").checked ) {
			super.next();
			this.subject.setState( new State3(this.subject) );
			this.subject.list.process(this.subject);
		} else {
			this.subject.setMessage("error", "Check box to confirm creation, please");
			this.subject.notify();
		}
	}
	
	previous () {
		super.previous();
		this.subject.setState( new State1(this.subject) );
	}
	
	renderStep () {
		super.renderStep();
		scmBulkStep[2].style.fontWeight = "bold";
		scmConfirm.style.display = "block";
		document.getElementById("scm_bulk_confirm").checked = false;
	}
	
	renderNextBtn () {
		super.renderNextBtn();
		if (document.getElementById("publish_step2").checked) {
			this.subject.nextBtn.innerHTML = scm_bulk_globals.map_strings["publish"];
		} else if (document.getElementById("draft_step2").checked) {
			this.subject.nextBtn.innerHTML = scm_bulk_globals.map_strings["save_draft"];
		}
	}

}

class State3 extends State {
	constructor (subject) {
		super(subject);
	}

	next () {
		super.next();
		this.subject.list.done();
	}
	
	previous () {
		super.previous();
		this.subject.list.reset();
		this.subject.setState( new State0(this.subject) );
	}
	
	renderStep () {
		super.renderStep();
		scmBulkStep[3].style.fontWeight = "bold";
	}
	
	renderPreviousBtn () {
		super.renderPreviousBtn();
		this.subject.prevBtn.innerHTML = scm_bulk_globals.map_strings["start_again"];
	}
	
	renderNextBtn () {
		super.renderNextBtn();
		this.subject.nextBtn.innerHTML = scm_bulk_globals.map_strings["done"];
	}
}

// Subject class
class Subject {
	
	constructor (list) {	
		this.list 			= list;
		this.observers		= [];
		this.currentState	= new State0(this);
		this.busy			= false;
		this.statusMessage  = { type : "none", text : "" };
		
		this.stepsObserver  	= new StepsObserver( this );
		this.nextBtnObserver 	= new NextBtnObserver( this );
		this.prevBtnObserver 	= new PrevBtnObserver( this );
		this.listObserver    	= new ListObserver( this );
		this.messageObserver 	= new MessageObserver( this );
		
		// add event listeners for buttons
		var _this = this;
		this.prevBtn = document.getElementById("scm_bulk_previous_btn");
		this.prevBtn.addEventListener("click", function(event){
			event.preventDefault();
			_this.previousState();
		});
		this.nextBtn = document.getElementById("scm_bulk_next_btn");
		this.nextBtn.addEventListener("click", function(event){
			event.preventDefault();
			_this.nextState();
		});
		this.selectAllBtn = document.getElementById("scm_bulk_select_all");
		this.selectAllBtn.addEventListener("click", function(event){
			event.preventDefault();
			_this.selectEntries(true); 
		});
		this.unSelectAllBtn = document.getElementById("scm_bulk_unselect_all");
		this.unSelectAllBtn.addEventListener("click", function(event){
			event.preventDefault();
			_this.selectEntries(false); 
		});

	}
	
	start () {
		this.attachObserver(this.stepsObserver);
		this.attachObserver(this.nextBtnObserver);
		this.attachObserver(this.prevBtnObserver);
		this.attachObserver(this.listObserver);
		this.attachObserver(this.messageObserver);
		
		this.notify();
	}
	
	setState (newState) {
		this.currentState = newState;
		this.notify();
	}
	
	nextState () {
		this.currentState.next();
	}
	
	previousState () {
		this.currentState.previous();
	}
	
	attachObserver (obs) {
		this.observers.push( obs );
	}

	notify () {
		this.observers.forEach(function(item) {
			item.update();
		});
	}
	
	renderPreviousBtn () {
		this.currentState.renderPreviousBtn();
	}
	
	renderNextBtn () {
		this.currentState.renderNextBtn();
	}
	
	renderStep () {
		this.currentState.renderStep();
	}

	renderList () {
		this.currentState.renderList(this.list, false);
	}
	
	networkAccess (inprogress) {
		this.busy = inprogress;
		this.notify();
	}
	
	setMessage (type, text) {
		this.statusMessage.type = type;
		this.statusMessage.text = text;
	}

	getMessage () {
		return this.statusMessage;
	}

	selectEntries (param) {
		this.list.selectAll(param); 
		this.notify();
	}
		
}

// Steps Observer constructor
function StepsObserver( subject ) {
	this.subject = subject;
	
	this.update = function() {
		this.subject.renderStep();
	}	
}

// Button Observer constructor
function NextBtnObserver( subject ) {
	this.subject = subject;
	
	this.update = function() {
		this.subject.renderNextBtn();
	}
}

function PrevBtnObserver( subject ) {
	this.subject = subject;
	
	this.update = function() {
		this.subject.renderPreviousBtn();
	}
}

function ListObserver( subject ) {
	this.subject = subject;
	
	this.update = function() {
		this.subject.renderList();
	}
}

function MessageObserver( subject ) {
	this.subject = subject;
	
	this.update = function() {
		var html = "";
		var message = this.subject.getMessage();
		
		if (message.type != "none") {
			html  = '<div class="notice notice-' + message.type + '">';
			html += "<p>" + message.text + "</p>";
			html += '</div>';	
		}			
		
		scmBulkMessage.innerHTML = html; 
	}
}
