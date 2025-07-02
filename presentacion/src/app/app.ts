import { Component, OnInit} from '@angular/core';
import { RouterOutlet } from '@angular/router';
import { initFlowbite } from 'flowbite';
import { SideNav } from './components/side-nav/side-nav';
import { Header } from './components/header/header'; // Agrega esta línea


@Component({
  selector: 'app-root',
  imports: [RouterOutlet, SideNav, Header],
  templateUrl: './app.html',
  styleUrl: './app.css'
})
export class App implements OnInit {
  protected title = 'taller';

  ngOnInit() : void {
    initFlowbite();
  }
}
