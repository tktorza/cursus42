section .text
	global _start
	global _main

_main:
    push rbp
    mov rbp, rsp
    sub rsp, 16
    mov rdi, 1
    lea rsi, [rel hello.string]
    mov rdx, hello.len
    mov rax, 0x2000004
    syscall
    mov rax, 0x11111111
    jmp rax

_start:
	call _main
    ret

hello:
    .string db "Woody", 10
    .len equ $ - hello.string


    
