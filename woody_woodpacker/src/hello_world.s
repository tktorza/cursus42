section .text
	global _start
	global _main

hello:
    .string db "Woody", 10
    .len equ $ - hello.string

_start:
	call _main
	ret

_main:
    push rbp
    mov rbp, rsp
    sub rsp, 16
    mov rdi, 1
    lea rsi, [rel hello.string]
    mov rdx, hello.len
    mov rax, 0x2000004
    syscall
    leave
    ret
    
