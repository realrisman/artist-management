import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { CelebrityVerboseLogComponent } from './celebrity-verbose-log.component';

describe('CelebrityVerboseLogComponent', () => {
  let component: CelebrityVerboseLogComponent;
  let fixture: ComponentFixture<CelebrityVerboseLogComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ CelebrityVerboseLogComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(CelebrityVerboseLogComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
