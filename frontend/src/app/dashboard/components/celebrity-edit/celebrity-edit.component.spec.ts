import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { CelebrityEditComponent } from './celebrity-edit.component';

describe('CelebrityEditComponent', () => {
  let component: CelebrityEditComponent;
  let fixture: ComponentFixture<CelebrityEditComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ CelebrityEditComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(CelebrityEditComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
